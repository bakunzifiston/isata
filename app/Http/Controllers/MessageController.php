<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMessageRequest;
use App\Http\Requests\UpdateMessageRequest;
use App\Jobs\SendMessageJob;
use App\Models\Channel;
use App\Models\Event;
use App\Models\Message;
use App\Services\ConnectionService;
use App\Support\StructuredMessageDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MessageController extends Controller
{
    public function index(Event $event): View
    {
        $this->authorize('viewAnyForEvent', [Message::class, $event]);

        $messages = $event->messages()
            ->with(['channel', 'sentBy', 'senderIdentity'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('messages.index', [
            'event' => $event,
            'messages' => $messages,
        ]);
    }

    public function create(Event $event): View
    {
        $this->authorize('createForEvent', [Message::class, $event]);

        $channels = Channel::orderBy('name')->get();
        $templates = auth()->user()->organization->messageTemplates()->with('channel')->get();
        $senderIdentities = $event->organization->emailSenderIdentities()->orderBy('label')->orderBy('from_name')->get();

        return view('messages.create', [
            'event' => $event,
            'channels' => $channels,
            'templates' => $templates,
            'senderIdentities' => $senderIdentities,
        ]);
    }

    public function store(StoreMessageRequest $request, Event $event): RedirectResponse
    {
        $organization = auth()->user()->organization;
        $channel = Channel::findOrFail($request->channel_id);

        $validated = $request->validated();

        $structuredDocument = null;
        if (($validated['content_type'] ?? null) === Message::CONTENT_TYPE_STRUCTURED) {
            $structuredDocument = $this->processStructuredSubmission($request, null);
            $validated['content'] = StructuredMessageDocument::buildPlainPreview($structuredDocument, 800);
        } elseif (($validated['content_type'] ?? null) === Message::CONTENT_TYPE_IMAGE && ! $request->hasFile('content_image')) {
            throw ValidationException::withMessages([
                'content_image' => ['Please upload an image for image content.'],
            ]);
        }

        $audioPath = null;
        if ($channel->supports_audio && $request->hasFile('audio_file')) {
            $audioPath = $request->file('audio_file')->store('messages/audio', 'public');
        }

        $attachmentPath = null;
        if ($channel->supports_attachment && $request->hasFile('attachment_file')) {
            $attachmentPath = $request->file('attachment_file')->store('messages/attachments', 'local');
        }

        $senderIdentityId = null;
        $senderCustomName = null;
        $senderCustomEmail = null;
        $senderUserId = null;

        if ($channel->slug === Channel::SLUG_EMAIL) {
            if (($validated['email_sender_type'] ?? '') === 'custom') {
                $senderCustomName = $validated['sender_custom_name'];
                $senderCustomEmail = $validated['sender_custom_email'];
            } else {
                $senderIdentityId = (int) $validated['sender_identity_id'];
            }

            $senderUserId = auth()->id();
        }

        $contentImagePath = ($validated['content_type'] ?? null) === Message::CONTENT_TYPE_STRUCTURED ? null :
            (($validated['content_type'] ?? null) === Message::CONTENT_TYPE_IMAGE && $request->hasFile('content_image')
                ? $request->file('content_image')->store('messages/content-images', 'public') : null);

        $bodyText = match ($validated['content_type']) {
            Message::CONTENT_TYPE_STRUCTURED => $validated['content'] ?? StructuredMessageDocument::buildPlainPreview(
                StructuredMessageDocument::normalize($structuredDocument ?? []),
                800
            ),
            Message::CONTENT_TYPE_TEXT => $validated['content'],
            default => $validated['content'] ?? '',
        };

        $event->messages()->create([
            'channel_id' => $validated['channel_id'],
            'sender_user_id' => $senderUserId,
            'sender_identity_id' => $senderIdentityId,
            'sender_custom_name' => $senderCustomName,
            'sender_custom_email' => $senderCustomEmail,
            'subject' => $validated['subject'] ?? null,
            'content' => $bodyText,
            'content_type' => $validated['content_type'],
            'content_document' => $structuredDocument,
            'content_image' => $contentImagePath,
            'audio_file' => $audioPath,
            'attachment_file' => $attachmentPath,
            'scheduled_at' => $validated['scheduled_at'] ?? null,
            'status' => $validated['status'],
        ]);

        return redirect()->route('events.messages.index', $event)
            ->with('status', 'Message created successfully.');
    }

    public function edit(Event $event, Message $message): View
    {
        $this->authorize('update', $message);

        if ($message->event_id !== $event->id) {
            abort(404);
        }

        $channels = Channel::orderBy('name')->get();
        $templates = auth()->user()->organization->messageTemplates()->with('channel')->get();
        $senderIdentities = $event->organization->emailSenderIdentities()->orderBy('label')->orderBy('from_name')->get();

        return view('messages.edit', [
            'event' => $event,
            'message' => $message,
            'channels' => $channels,
            'templates' => $templates,
            'senderIdentities' => $senderIdentities,
        ]);
    }

    public function update(UpdateMessageRequest $request, Event $event, Message $message): RedirectResponse
    {
        if ($message->event_id !== $event->id) {
            abort(403);
        }

        $organization = auth()->user()->organization;
        $channel = Channel::findOrFail($request->channel_id);

        $validated = $request->validated();

        if ($validated['content_type'] === Message::CONTENT_TYPE_STRUCTURED && $message->content_type !== Message::CONTENT_TYPE_STRUCTURED && $message->content_image) {
            Storage::disk('public')->delete($message->content_image);
        }

        if ($validated['content_type'] !== Message::CONTENT_TYPE_STRUCTURED && $message->isStructuredContent()) {
            $message->deleteStructuredImageFiles();
        }

        $structuredDocument = null;
        if (($validated['content_type'] ?? null) === Message::CONTENT_TYPE_STRUCTURED) {
            $structuredDocument = $this->processStructuredSubmission($request, $message);
            $validated['content'] = StructuredMessageDocument::buildPlainPreview($structuredDocument, 800);
        } elseif (($validated['content_type'] ?? null) === Message::CONTENT_TYPE_IMAGE) {
            $willHaveImage = $request->hasFile('content_image')
                || ($message->content_image && ! $request->boolean('remove_content_image'));
            if (! $willHaveImage) {
                throw ValidationException::withMessages([
                    'content_image' => ['Upload an image, or leave the existing image in place (uncheck remove).'],
                ]);
            }
        }

        $senderIdentityId = null;
        $senderCustomName = null;
        $senderCustomEmail = null;
        $senderUserId = null;

        if ($channel->slug === Channel::SLUG_EMAIL) {
            if (($validated['email_sender_type'] ?? '') === 'custom') {
                $senderCustomName = $validated['sender_custom_name'];
                $senderCustomEmail = $validated['sender_custom_email'];
            } else {
                $senderIdentityId = (int) $validated['sender_identity_id'];
            }

            $senderUserId = auth()->id();
        }

        $bodyText = match ($validated['content_type']) {
            Message::CONTENT_TYPE_STRUCTURED => $validated['content'] ?? '',
            Message::CONTENT_TYPE_TEXT => $validated['content'],
            default => $validated['content'] ?? '',
        };

        $contentImagePath = $message->content_image;
        $contentDocValue = null;

        if (($validated['content_type'] ?? null) === Message::CONTENT_TYPE_STRUCTURED) {
            if ($contentImagePath) {
                Storage::disk('public')->delete($contentImagePath);
            }
            $contentImagePath = null;
            $contentDocValue = $structuredDocument;
        } elseif ($validated['content_type'] === Message::CONTENT_TYPE_TEXT) {
            if ($contentImagePath) {
                Storage::disk('public')->delete($contentImagePath);
            }
            $contentImagePath = null;
        } elseif ($validated['content_type'] === Message::CONTENT_TYPE_IMAGE) {
            $contentDocValue = null;
            if ($request->boolean('remove_content_image')) {
                if ($contentImagePath) {
                    Storage::disk('public')->delete($contentImagePath);
                }
                $contentImagePath = null;
            }
            if ($request->hasFile('content_image')) {
                if ($contentImagePath) {
                    Storage::disk('public')->delete($contentImagePath);
                }
                $contentImagePath = $request->file('content_image')->store('messages/content-images', 'public');
            }
        }

        $data = [
            'channel_id' => $validated['channel_id'],
            'sender_user_id' => $senderUserId,
            'sender_identity_id' => $senderIdentityId,
            'sender_custom_name' => $senderCustomName,
            'sender_custom_email' => $senderCustomEmail,
            'subject' => $validated['subject'] ?? null,
            'content' => $bodyText,
            'content_type' => $validated['content_type'],
            'content_document' => $contentDocValue,
            'content_image' => $contentImagePath,
            'scheduled_at' => $validated['scheduled_at'] ?? null,
            'status' => $validated['status'],
        ];

        if ($channel->supports_audio && $request->hasFile('audio_file')) {
            if ($message->audio_file) {
                Storage::disk('public')->delete($message->audio_file);
            }
            $data['audio_file'] = $request->file('audio_file')->store('messages/audio', 'public');
        }

        if (! $channel->supports_attachment) {
            if ($message->attachment_file) {
                $this->deleteMessageAttachment($message->attachment_file);
            }
            $data['attachment_file'] = null;
        } elseif ($request->boolean('remove_attachment')) {
            if ($message->attachment_file) {
                $this->deleteMessageAttachment($message->attachment_file);
            }
            $data['attachment_file'] = null;
        } elseif ($request->hasFile('attachment_file')) {
            if ($message->attachment_file) {
                $this->deleteMessageAttachment($message->attachment_file);
            }
            $data['attachment_file'] = $request->file('attachment_file')->store('messages/attachments', 'local');
        }

        $message->update($data);

        return redirect()->route('events.messages.index', $event)
            ->with('status', 'Message updated.');
    }

    public function sendNow(Event $event, Message $message): RedirectResponse
    {
        $this->authorize('sendNow', $message);

        if ($message->event_id !== $event->id) {
            abort(403);
        }

        if ($message->status === Message::STATUS_SENT) {
            return redirect()->route('events.messages.index', $event)
                ->with('error', 'Message already sent.');
        }

        $connection = app(ConnectionService::class);

        if (! $connection->isOnline()) {
            $message->update([
                'status' => Message::STATUS_QUEUED,
                'scheduled_at' => now(),
            ]);

            return redirect()->route('events.messages.index', $event)
                ->with('status', 'Message queued for delivery when connection is restored.');
        }

        $message->update([
            'status' => Message::STATUS_SCHEDULED,
            'scheduled_at' => now(),
        ]);

        SendMessageJob::dispatch($message->fresh());

        return redirect()->route('events.messages.index', $event)
            ->with('status', 'Message sent.');
    }

    public function destroy(Event $event, Message $message): RedirectResponse
    {
        $this->authorize('delete', $message);

        if ($message->event_id !== $event->id) {
            abort(403);
        }

        if ($message->audio_file) {
            Storage::disk('public')->delete($message->audio_file);
        }

        if ($message->attachment_file) {
            $this->deleteMessageAttachment($message->attachment_file);
        }

        if ($message->content_image) {
            Storage::disk('public')->delete($message->content_image);
        }

        if ($message->isStructuredContent()) {
            $message->deleteStructuredImageFiles();
        }

        $message->delete();

        return redirect()->route('events.messages.index', $event)
            ->with('status', 'Message deleted.');
    }

    /**
     * Parses JSON + attaches uploads + deletes orphaned images + validates design rules.
     *
     * @return array{v: int, blocks: array}
     */
    private function processStructuredSubmission(Request $request, ?Message $existing): array
    {
        $decoded = json_decode($request->input('content_document', ''), true);
        if (! is_array($decoded)) {
            throw ValidationException::withMessages([
                'content_document' => ['Structured content body must be valid JSON.'],
            ]);
        }

        $normalized = StructuredMessageDocument::normalize($decoded);

        $withDocs = StructuredMessageDocument::attachUploadedImages($normalized, $request);

        if ($existing?->content_document) {
            $oldPaths = StructuredMessageDocument::collectImagePaths(
                StructuredMessageDocument::normalize($existing->content_document)
            );
            $newPaths = StructuredMessageDocument::collectImagePaths($withDocs);
            foreach (array_diff($oldPaths, $newPaths) as $orphan) {
                Storage::disk('public')->delete($orphan);
            }
        }

        StructuredMessageDocument::validateAfterUpload($withDocs);

        return $withDocs;
    }

    private function deleteMessageAttachment(string $path): void
    {
        if (Storage::disk('local')->exists($path)) {
            Storage::disk('local')->delete($path);
        }

        // Legacy rows may still reference the public disk (pre–Phase 0).
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Jobs\SendMessageJob;
use App\Models\Channel;
use App\Models\Event;
use App\Models\Message;
use App\Models\MessageTemplate;
use App\Services\ConnectionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MessageController extends Controller
{
    public function index(Event $event): View
    {
        $this->authorizeEvent($event);

        $messages = $event->messages()->with('channel')->orderBy('created_at', 'desc')->paginate(15);

        return view('messages.index', [
            'event' => $event,
            'messages' => $messages,
        ]);
    }

    public function create(Event $event): View
    {
        $this->authorizeEvent($event);

        $channels = Channel::orderBy('name')->get();
        $templates = auth()->user()->organization->messageTemplates()->with('channel')->get();

        return view('messages.create', [
            'event' => $event,
            'channels' => $channels,
            'templates' => $templates,
        ]);
    }

    public function store(Request $request, Event $event): RedirectResponse
    {
        $this->authorizeEvent($event);

        $channel = Channel::findOrFail($request->channel_id);

        $rules = [
            'channel_id' => ['required', 'exists:channels,id'],
            'content' => ['required', 'string', 'max:10000'],
            'scheduled_at' => ['nullable', 'date'],
            'status' => ['required', 'in:draft,scheduled'],
        ];

        if ($channel->supports_subject) {
            $rules['subject'] = ['nullable', 'string', 'max:255'];
        }

        if ($channel->supports_audio) {
            $rules['audio_file'] = ['nullable', 'file', 'mimes:mp3,wav,m4a', 'max:10240'];
        }

        $validated = $request->validate($rules);

        $audioPath = null;
        if ($request->hasFile('audio_file')) {
            $audioPath = $request->file('audio_file')->store('messages/audio', 'public');
        }

        $event->messages()->create([
            'channel_id' => $validated['channel_id'],
            'subject' => $validated['subject'] ?? null,
            'content' => $validated['content'],
            'audio_file' => $audioPath,
            'scheduled_at' => $validated['scheduled_at'] ?? null,
            'status' => $validated['status'],
        ]);

        return redirect()->route('events.messages.index', $event)
            ->with('status', 'Message created successfully.');
    }

    public function edit(Event $event, Message $message): View
    {
        $this->authorizeEvent($event);

        if ($message->event_id !== $event->id) {
            abort(404);
        }

        $channels = Channel::orderBy('name')->get();
        $templates = auth()->user()->organization->messageTemplates()->with('channel')->get();

        return view('messages.edit', [
            'event' => $event,
            'message' => $message,
            'channels' => $channels,
            'templates' => $templates,
        ]);
    }

    public function update(Request $request, Event $event, Message $message): RedirectResponse
    {
        $this->authorizeEvent($event);

        if ($message->event_id !== $event->id) {
            abort(403);
        }

        $channel = Channel::findOrFail($request->channel_id);

        $rules = [
            'channel_id' => ['required', 'exists:channels,id'],
            'content' => ['required', 'string', 'max:10000'],
            'scheduled_at' => ['nullable', 'date'],
            'status' => ['required', 'in:draft,scheduled,queued,sent,failed'],
        ];

        if ($channel->supports_subject) {
            $rules['subject'] = ['nullable', 'string', 'max:255'];
        }

        if ($channel->supports_audio) {
            $rules['audio_file'] = ['nullable', 'file', 'mimes:mp3,wav,m4a', 'max:10240'];
        }

        $validated = $request->validate($rules);

        $data = [
            'channel_id' => $validated['channel_id'],
            'subject' => $validated['subject'] ?? null,
            'content' => $validated['content'],
            'scheduled_at' => $validated['scheduled_at'] ?? null,
            'status' => $validated['status'],
        ];

        if ($request->hasFile('audio_file')) {
            if ($message->audio_file) {
                Storage::disk('public')->delete($message->audio_file);
            }
            $data['audio_file'] = $request->file('audio_file')->store('messages/audio', 'public');
        }

        $message->update($data);

        return redirect()->route('events.messages.index', $event)
            ->with('status', 'Message updated.');
    }

    public function sendNow(Event $event, Message $message): RedirectResponse
    {
        $this->authorizeEvent($event);

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
        $this->authorizeEvent($event);

        if ($message->event_id !== $event->id) {
            abort(403);
        }

        if ($message->audio_file) {
            Storage::disk('public')->delete($message->audio_file);
        }

        $message->delete();

        return redirect()->route('events.messages.index', $event)
            ->with('status', 'Message deleted.');
    }

    private function authorizeEvent(Event $event): void
    {
        $organization = auth()->user()->organization;
        if (! $organization || $event->organization_id !== $organization->id) {
            abort(404);
        }
    }
}

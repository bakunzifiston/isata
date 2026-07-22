<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSocialPostRequest;
use App\Http\Requests\UpdateSocialPostRequest;
use App\Jobs\PublishSocialPostJob;
use App\Models\Channel;
use App\Models\Event;
use App\Models\Message;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SocialPostController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Message::class);

        $organization = auth()->user()->organization;
        $channelId = $this->socialChannelId();

        $posts = Message::query()
            ->where('organization_id', $organization->id)
            ->where('channel_id', $channelId)
            ->with(['event', 'socialAccount'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('social.index', [
            'posts' => $posts,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Message::class);

        $organization = auth()->user()->organization;

        $events = $organization->events()
            ->whereIn('status', [Event::STATUS_SCHEDULED, Event::STATUS_COMPLETED])
            ->orderByDesc('date')
            ->get();

        $accounts = $organization->socialAccounts()->where('is_active', true)->get();

        return view('social.create', [
            'events' => $events,
            'accounts' => $accounts,
            'preselectedEventId' => $request->input('event_id'),
            'post' => new Message([
                'event_id' => $request->input('event_id'),
                'social_platform' => 'facebook',
                'content_type' => Message::CONTENT_TYPE_TEXT,
            ]),
        ]);
    }

    public function store(StoreSocialPostRequest $request): RedirectResponse
    {
        $organization = auth()->user()->organization;
        $validated = $request->validated();

        if (! empty($validated['event_id'])) {
            $linkedEvent = Event::find($validated['event_id']);
            if (! $linkedEvent || $linkedEvent->organization_id !== $organization->id) {
                abort(404);
            }
        } elseif (empty($validated['event_id'])) {
            return redirect()->back()->withErrors(['event_id' => 'Select an event for this social post.'])->withInput();
        }

        $mediaPaths = [];
        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                $mediaPaths[] = $file->store('social/media', 'public');
            }
        }

        $content = $this->renderContent($validated['content'], $validated['event_id'] ?? null);

        Message::create([
            'organization_id' => $organization->id,
            'event_id' => $validated['event_id'],
            'channel_id' => $this->socialChannelId(),
            'social_account_id' => $validated['social_account_id'] ?? null,
            'social_platform' => $validated['platform'],
            'content' => $content,
            'content_type' => Message::CONTENT_TYPE_TEXT,
            'media_paths' => $mediaPaths ?: null,
            'scheduled_at' => $validated['scheduled_at'] ?? null,
            'status' => $validated['status'],
        ]);

        return redirect()->route('social.index')->with('status', 'Post created.');
    }

    public function edit(int $post): View
    {
        $message = $this->resolveSocialMessage($post);
        $this->authorize('update', $message);

        $organization = auth()->user()->organization;

        $events = $organization->events()
            ->whereIn('status', [Event::STATUS_SCHEDULED, Event::STATUS_COMPLETED])
            ->orderByDesc('date')
            ->get();

        $accounts = $organization->socialAccounts()->where('is_active', true)->get();

        return view('social.edit', [
            'post' => $message,
            'events' => $events,
            'accounts' => $accounts,
        ]);
    }

    public function update(UpdateSocialPostRequest $request, int $post): RedirectResponse
    {
        $message = $this->resolveSocialMessage($post);

        $organization = auth()->user()->organization;
        $validated = $request->validated();

        if (! empty($validated['event_id'])) {
            $linkedEvent = Event::find($validated['event_id']);
            if (! $linkedEvent || $linkedEvent->organization_id !== $organization->id) {
                abort(404);
            }
        }

        $mediaPaths = $message->media_paths ?? [];
        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                $mediaPaths[] = $file->store('social/media', 'public');
            }
        }

        $content = $this->renderContent($validated['content'], $validated['event_id'] ?? null);

        $status = $validated['status'] === 'published' ? Message::STATUS_SENT : $validated['status'];

        $message->update([
            'event_id' => $validated['event_id'] ?? null,
            'social_account_id' => $validated['social_account_id'] ?? null,
            'social_platform' => $validated['platform'],
            'content' => $content,
            'media_paths' => $mediaPaths ?: null,
            'scheduled_at' => $validated['scheduled_at'] ?? null,
            'status' => $status,
        ]);

        return redirect()->route('social.index')->with('status', 'Post updated.');
    }

    public function destroy(int $post): RedirectResponse
    {
        $message = $this->resolveSocialMessage($post);
        $this->authorize('delete', $message);

        if ($message->media_paths) {
            foreach ($message->media_paths as $path) {
                Storage::disk('public')->delete($path);
            }
        }

        $message->delete();

        return redirect()->route('social.index')->with('status', 'Post deleted.');
    }

    public function publishNow(int $post): RedirectResponse
    {
        $message = $this->resolveSocialMessage($post);
        $this->authorize('sendNow', $message);

        if ($message->status === Message::STATUS_SENT) {
            return redirect()->route('social.index')->with('error', 'Post already published.');
        }

        $message->update(['status' => Message::STATUS_SCHEDULED, 'scheduled_at' => now()]);
        PublishSocialPostJob::dispatch($message);

        return redirect()->route('social.index')->with('status', 'Post published.');
    }

    protected function renderContent(string $content, ?int $eventId): string
    {
        if ($eventId) {
            $event = Event::find($eventId);
            $rsvpUrl = route('events.rsvp', $eventId);
            $content = str_replace('{rsvp_link}', $rsvpUrl, $content);
            $content = str_replace('{event_link}', $rsvpUrl, $content);
            if ($event) {
                $content = str_replace('{event_name}', $event->name, $content);
                $content = str_replace('{event_time}', $event->date?->format('M j, Y').($event->time_formatted ? ' at '.$event->time_formatted : ''), $content);
            }
        }

        return $content;
    }

    private function socialChannelId(): int
    {
        return (int) Channel::where('slug', Channel::SLUG_SOCIAL_MEDIA)->value('id');
    }

    private function resolveSocialMessage(int $id): Message
    {
        $organization = auth()->user()->organization;

        return Message::query()
            ->where('id', $id)
            ->where('organization_id', $organization->id)
            ->where('channel_id', $this->socialChannelId())
            ->firstOrFail();
    }
}

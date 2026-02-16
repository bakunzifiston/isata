<?php

namespace App\Http\Controllers;

use App\Jobs\PublishSocialPostJob;
use App\Models\Event;
use App\Models\SocialAccount;
use App\Models\SocialPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SocialPostController extends Controller
{
    public function index(Request $request): View
    {
        $organization = auth()->user()->organization;

        if (! $organization) {
            abort(403);
        }

        $posts = SocialPost::where('organization_id', $organization->id)
            ->with(['event', 'socialAccount'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('social.index', [
            'posts' => $posts,
        ]);
    }

    public function create(Request $request): View
    {
        $organization = auth()->user()->organization;

        if (! $organization) {
            abort(403);
        }

        $events = $organization->events()
            ->whereIn('status', [Event::STATUS_SCHEDULED, Event::STATUS_COMPLETED])
            ->orderByDesc('date')
            ->get();

        $accounts = $organization->socialAccounts()->where('is_active', true)->get();

        return view('social.create', [
            'events' => $events,
            'accounts' => $accounts,
            'preselectedEventId' => $request->input('event_id'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $organization = auth()->user()->organization;

        if (! $organization) {
            abort(403);
        }

        $validated = $request->validate([
            'platform' => ['required', 'in:facebook,linkedin,twitter,whatsapp'],
            'content' => ['required', 'string', 'max:10000'],
            'event_id' => ['nullable', 'exists:events,id'],
            'social_account_id' => ['nullable', 'exists:social_accounts,id'],
            'scheduled_at' => ['nullable', 'date'],
            'status' => ['required', 'in:draft,scheduled'],
            'media' => ['nullable', 'array'],
            'media.*' => ['file', 'image', 'max:10240'],
        ]);

        $mediaPaths = [];
        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                $mediaPaths[] = $file->store('social/media', 'public');
            }
        }

        $content = $this->renderContent($validated['content'], $validated['event_id'] ?? null);

        $post = $organization->socialPosts()->create([
            'event_id' => $validated['event_id'] ?? null,
            'social_account_id' => $validated['social_account_id'] ?? null,
            'platform' => $validated['platform'],
            'content' => $content,
            'media_paths' => $mediaPaths ?: null,
            'scheduled_at' => $validated['scheduled_at'] ?? null,
            'status' => $validated['status'],
        ]);

        return redirect()->route('social.index')->with('status', 'Post created.');
    }

    public function edit(SocialPost $post): View
    {
        $organization = auth()->user()->organization;

        if (! $organization || $post->organization_id !== $organization->id) {
            abort(404);
        }

        $events = $organization->events()
            ->whereIn('status', [Event::STATUS_SCHEDULED, Event::STATUS_COMPLETED])
            ->orderByDesc('date')
            ->get();

        $accounts = $organization->socialAccounts()->where('is_active', true)->get();

        return view('social.edit', [
            'post' => $post,
            'events' => $events,
            'accounts' => $accounts,
        ]);
    }

    public function update(Request $request, SocialPost $post): RedirectResponse
    {
        $organization = auth()->user()->organization;

        if (! $organization || $post->organization_id !== $organization->id) {
            abort(403);
        }

        $validated = $request->validate([
            'platform' => ['required', 'in:facebook,linkedin,twitter,whatsapp'],
            'content' => ['required', 'string', 'max:10000'],
            'event_id' => ['nullable', 'exists:events,id'],
            'social_account_id' => ['nullable', 'exists:social_accounts,id'],
            'scheduled_at' => ['nullable', 'date'],
            'status' => ['required', 'in:draft,scheduled,published,failed'],
            'media' => ['nullable', 'array'],
            'media.*' => ['file', 'image', 'max:10240'],
        ]);

        $mediaPaths = $post->media_paths ?? [];
        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                $mediaPaths[] = $file->store('social/media', 'public');
            }
        }

        $content = $this->renderContent($validated['content'], $validated['event_id'] ?? null);

        $post->update([
            'event_id' => $validated['event_id'] ?? null,
            'social_account_id' => $validated['social_account_id'] ?? null,
            'platform' => $validated['platform'],
            'content' => $content,
            'media_paths' => $mediaPaths ?: null,
            'scheduled_at' => $validated['scheduled_at'] ?? null,
            'status' => $validated['status'],
        ]);

        return redirect()->route('social.index')->with('status', 'Post updated.');
    }

    public function destroy(SocialPost $post): RedirectResponse
    {
        $organization = auth()->user()->organization;

        if (! $organization || $post->organization_id !== $organization->id) {
            abort(403);
        }

        if ($post->media_paths) {
            foreach ($post->media_paths as $path) {
                Storage::disk('public')->delete($path);
            }
        }

        $post->delete();

        return redirect()->route('social.index')->with('status', 'Post deleted.');
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
                $content = str_replace('{event_time}', $event->date?->format('M j, Y') . ($event->time_formatted ? ' at ' . $event->time_formatted : ''), $content);
            }
        }
        return $content;
    }

    public function publishNow(SocialPost $post): RedirectResponse
    {
        $organization = auth()->user()->organization;

        if (! $organization || $post->organization_id !== $organization->id) {
            abort(403);
        }

        if ($post->status === SocialPost::STATUS_PUBLISHED) {
            return redirect()->route('social.index')->with('error', 'Post already published.');
        }

        $post->update(['status' => SocialPost::STATUS_SCHEDULED, 'scheduled_at' => now()]);
        PublishSocialPostJob::dispatch($post);

        return redirect()->route('social.index')->with('status', 'Post published.');
    }
}

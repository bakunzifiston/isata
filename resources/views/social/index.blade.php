@extends('layouts.dashboard')

@section('title', 'Social Posts - ' . config('app.name'))

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
    <div>
        <h1 class="admin-page-title">Social media</h1>
        <p class="admin-page-subtitle">Schedule and publish posts to Facebook, LinkedIn, Twitter, WhatsApp</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('social.accounts') }}" class="admin-btn-secondary">Accounts</a>
        <a href="{{ route('social.create') }}" class="admin-btn-primary">Create post</a>
    </div>
</div>

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Platform</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Content</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Event</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Scheduled</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Engagement</th>
                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-ink/50">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($posts as $post)
            @php $displayStatus = $post->status === 'sent' ? 'published' : $post->status; @endphp
            <tr>
                <td class="px-6 py-4">
                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-dawn-2 text-ink/70">
                        {{ ucfirst($post->social_platform ?? 'social') }}
                    </span>
                </td>
                <td class="px-6 py-4 text-sm text-ink/65 truncate max-w-xs">{{ Str::limit($post->content, 50) }}</td>
                <td class="px-6 py-4 text-sm text-ink/65">{{ $post->event?->name ?? '—' }}</td>
                <td class="px-6 py-4 text-sm text-ink/65">{{ $post->scheduled_at?->format('M j, H:i') ?? '—' }}</td>
                <td class="px-6 py-4">
                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full
                        {{ $displayStatus === 'draft' ? 'bg-amber-100 text-amber-800' : '' }}
                        {{ $displayStatus === 'scheduled' ? 'bg-blue-100 text-blue-800' : '' }}
                        {{ $displayStatus === 'published' ? 'bg-emerald-100 text-emerald-800' : '' }}
                        {{ $displayStatus === 'failed' ? 'bg-red-100 text-red-800' : '' }}
                    ">{{ ucfirst($displayStatus) }}</span>
                </td>
                <td class="px-6 py-4 text-sm text-ink/65">{{ $post->total_engagement }}</td>
                <td class="px-6 py-4 text-right text-sm space-x-2">
                    @if(in_array($post->status, ['draft', 'scheduled']))
                    <form method="POST" action="{{ route('social.publish-now', $post) }}" class="inline">
                        @csrf
                        <button type="submit" class="admin-link">Publish now</button>
                    </form>
                    @endif
                    <a href="{{ route('social.edit', $post) }}" class="admin-link">Edit</a>
                    <form method="POST" action="{{ route('social.destroy', $post) }}" class="inline" onsubmit="return confirm('Delete this post?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-6 py-12 text-center text-ink/50">
                    No posts yet. <a href="{{ route('social.create') }}" class="admin-link">Create your first post</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($posts->hasPages())
    <div class="px-6 py-4 border-t border-dawn-2/80">{{ $posts->links() }}</div>
    @endif
</div>
@endsection

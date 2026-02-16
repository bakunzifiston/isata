@extends('layouts.dashboard')

@section('title', 'Social Posts - ' . config('app.name'))

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Social media</h1>
        <p class="mt-1 text-slate-600">Schedule and publish posts to Facebook, LinkedIn, Twitter, WhatsApp</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('social.accounts') }}" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 font-medium hover:bg-slate-50">Accounts</a>
        <a href="{{ route('social.create') }}" class="px-4 py-2 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-700">Create post</a>
    </div>
</div>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <table class="min-w-full divide-y divide-slate-200">
        <thead class="bg-slate-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Platform</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Content</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Event</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Scheduled</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Engagement</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            @forelse($posts as $post)
            <tr class="hover:bg-slate-50">
                <td class="px-6 py-4">
                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-slate-100 text-slate-800">
                        {{ ucfirst($post->platform) }}
                    </span>
                </td>
                <td class="px-6 py-4 text-sm text-slate-600 truncate max-w-xs">{{ Str::limit($post->content, 50) }}</td>
                <td class="px-6 py-4 text-sm text-slate-600">{{ $post->event?->name ?? '—' }}</td>
                <td class="px-6 py-4 text-sm text-slate-600">{{ $post->scheduled_at?->format('M j, H:i') ?? '—' }}</td>
                <td class="px-6 py-4">
                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full
                        {{ $post->status === 'draft' ? 'bg-amber-100 text-amber-800' : '' }}
                        {{ $post->status === 'scheduled' ? 'bg-blue-100 text-blue-800' : '' }}
                        {{ $post->status === 'published' ? 'bg-emerald-100 text-emerald-800' : '' }}
                        {{ $post->status === 'failed' ? 'bg-red-100 text-red-800' : '' }}
                    ">{{ ucfirst($post->status) }}</span>
                </td>
                <td class="px-6 py-4 text-sm text-slate-600">{{ $post->total_engagement }}</td>
                <td class="px-6 py-4 text-right text-sm space-x-2">
                    @if(in_array($post->status, ['draft', 'scheduled']))
                    <form method="POST" action="{{ route('social.publish-now', $post) }}" class="inline">
                        @csrf
                        <button type="submit" class="text-indigo-600 hover:text-indigo-900">Publish now</button>
                    </form>
                    @endif
                    <a href="{{ route('social.edit', $post) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                    <form method="POST" action="{{ route('social.destroy', $post) }}" class="inline" onsubmit="return confirm('Delete this post?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                    No posts yet. <a href="{{ route('social.create') }}" class="text-indigo-600 hover:text-indigo-900">Create your first post</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($posts->hasPages())
    <div class="px-6 py-4 border-t border-slate-200">{{ $posts->links() }}</div>
    @endif
</div>
@endsection

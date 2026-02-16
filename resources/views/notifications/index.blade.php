@extends('layouts.dashboard')

@section('title', 'Notifications - ' . config('app.name'))

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Notifications</h1>
        <p class="mt-1 text-slate-600">Your activity and system notifications</p>
    </div>
    <form method="POST" action="{{ route('notifications.mark-read') }}">
        @csrf
        <button type="submit" class="px-4 py-2 rounded-lg bg-slate-100 text-slate-700 text-sm font-medium hover:bg-slate-200">
            Mark all as read
        </button>
    </form>
</div>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <ul class="divide-y divide-slate-200">
        @forelse($notifications as $notification)
            @php
                $data = $notification->data;
                $isUnread = !$notification->read_at;
            @endphp
            <li class="{{ $isUnread ? 'bg-indigo-50/30' : '' }}">
                <a href="{{ $data['url'] ?? '#' }}" class="block px-6 py-4 hover:bg-slate-50">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-slate-900">{{ $data['title'] ?? 'Notification' }}</p>
                            <p class="mt-0.5 text-sm text-slate-600">{{ $data['message'] ?? $data['body'] ?? '' }}</p>
                            <p class="mt-1 text-xs text-slate-400">{{ $notification->created_at->diffForHumans() }}</p>
                        </div>
                        @if($isUnread)
                            <span class="shrink-0 w-2 h-2 rounded-full bg-indigo-500 mt-2"></span>
                        @endif
                    </div>
                </a>
            </li>
        @empty
            <li class="px-6 py-12 text-center text-slate-500">No notifications yet</li>
        @endforelse
    </ul>
    @if($notifications->hasPages())
        <div class="px-6 py-4 border-t border-slate-200">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection

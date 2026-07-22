@extends('layouts.dashboard')

@section('title', 'Notifications - ' . config('app.name'))

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="admin-page-title">Notifications</h1>
        <p class="admin-page-subtitle">Your activity and system notifications</p>
    </div>
    <form method="POST" action="{{ route('notifications.mark-read') }}">
        @csrf
        <button type="submit" class="px-4 py-2 rounded-lg bg-dawn-2 text-ink/75 text-sm font-medium hover:bg-dawn-2/80">
            Mark all as read
        </button>
    </form>
</div>

<div class="admin-table-wrap">
    <ul class="divide-y divide-dawn-2/80">
        @forelse($notifications as $notification)
            @php
                $data = $notification->data;
                $isUnread = !$notification->read_at;
            @endphp
            <li class="{{ $isUnread ? 'bg-coral/5' : '' }}">
                <a href="{{ $data['url'] ?? '#' }}" class="block px-6 py-4 hover:bg-dawn-2/30">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-ink">{{ $data['title'] ?? 'Notification' }}</p>
                            <p class="mt-0.5 text-sm text-ink/65">{{ $data['message'] ?? $data['body'] ?? '' }}</p>
                            <p class="mt-1 text-xs text-ink/45">{{ $notification->created_at->diffForHumans() }}</p>
                        </div>
                        @if($isUnread)
                            <span class="shrink-0 mt-2 h-2 w-2 rounded-full bg-coral"></span>
                        @endif
                    </div>
                </a>
            </li>
        @empty
            <li class="px-6 py-12 text-center text-ink/50">No notifications yet</li>
        @endforelse
    </ul>
    @if($notifications->hasPages())
        <div class="px-6 py-4 border-t border-dawn-2/80">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection

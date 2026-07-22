<div class="relative" x-data="{ open: false }">
    <button type="button" @click="open = !open" class="relative rounded-lg p-2 text-ink/70 transition hover:bg-canvas-2/80 hover:text-ink">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        @if(isset($unreadNotificationsCount) && $unreadNotificationsCount > 0)
            <span class="absolute -right-0.5 -top-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-coral text-[10px] font-bold text-white">{{ $unreadNotificationsCount > 9 ? '9+' : $unreadNotificationsCount }}</span>
        @endif
    </button>
    <div x-show="open" x-cloak @click.away="open = false"
         class="absolute right-0 z-50 mt-2 w-80 rounded-xl border border-dawn-2 bg-white py-2 shadow-lg ring-1 ring-ink/5">
        <div class="border-b border-dawn-2/80 px-4 py-3">
            <h3 class="font-display text-sm font-semibold text-ink">Notifications</h3>
        </div>
        <div class="max-h-80 overflow-y-auto">
            @forelse($notifications ?? [] as $notification)
                <a href="{{ $notification['url'] ?? '#' }}" class="block px-4 py-3 transition hover:bg-canvas/80 {{ $notification['read_at'] ? '' : 'bg-coral/5' }}">
                    <p class="text-sm font-medium text-ink">{{ $notification['title'] ?? 'Notification' }}</p>
                    <p class="mt-0.5 text-xs text-ink/60">{{ $notification['body'] ?? '' }}</p>
                    <p class="mt-1 text-xs text-ink/40">{{ $notification['created_at'] ?? '' }}</p>
                </a>
            @empty
                <div class="px-4 py-8 text-center text-sm text-ink/50">No notifications yet</div>
            @endforelse
        </div>
        <div class="border-t border-dawn-2/80 px-4 py-2">
            <a href="{{ route('notifications.index') }}" class="text-sm font-medium text-coral hover:text-coral/80">View all</a>
        </div>
    </div>
</div>

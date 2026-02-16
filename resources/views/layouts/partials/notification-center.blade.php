<div class="relative" x-data="{ open: false }">
    <button type="button" @click="open = !open" class="relative p-2 rounded-lg text-slate-600 hover:bg-slate-100 hover:text-slate-900">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        @if(isset($unreadNotificationsCount) && $unreadNotificationsCount > 0)
            <span class="absolute -top-0.5 -right-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-indigo-600 text-[10px] font-bold text-white">{{ $unreadNotificationsCount > 9 ? '9+' : $unreadNotificationsCount }}</span>
        @endif
    </button>
    <div x-show="open" x-cloak @click.away="open = false"
         class="absolute right-0 mt-2 w-80 rounded-xl bg-white shadow-lg ring-1 ring-slate-200 py-2 z-50">
        <div class="px-4 py-3 border-b border-slate-100">
            <h3 class="text-sm font-semibold text-slate-900">Notifications</h3>
        </div>
        <div class="max-h-80 overflow-y-auto">
            @forelse($notifications ?? [] as $notification)
                <a href="{{ $notification['url'] ?? '#' }}" class="block px-4 py-3 hover:bg-slate-50 {{ $notification['read_at'] ? '' : 'bg-indigo-50/50' }}">
                    <p class="text-sm font-medium text-slate-900">{{ $notification['title'] ?? 'Notification' }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">{{ $notification['body'] ?? '' }}</p>
                    <p class="text-xs text-slate-400 mt-1">{{ $notification['created_at'] ?? '' }}</p>
                </a>
            @empty
                <div class="px-4 py-8 text-center text-sm text-slate-500">No notifications yet</div>
            @endforelse
        </div>
        <div class="border-t border-slate-100 px-4 py-2">
            <a href="{{ route('notifications.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">View all</a>
        </div>
    </div>
</div>

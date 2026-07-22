@php
    $navActive = fn (string $pattern, bool $unless = false): string => request()->routeIs($pattern) && ! $unless
        ? 'admin-nav-link admin-nav-link-active'
        : 'admin-nav-link';
@endphp

<aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-64 -translate-x-full transform bg-dusk text-dawn transition-transform duration-200 lg:translate-x-0">
    <div class="flex h-full flex-col">
        <div class="flex items-center gap-3 border-b border-dawn/10 px-5 py-5">
            <a href="{{ route('dashboard') }}" class="flex min-w-0 items-center gap-3">
                <span class="relative flex h-9 w-9 shrink-0 items-center justify-center">
                    <span class="absolute inset-0 rounded-full border-2 border-dawn/25"></span>
                    <span class="absolute inset-1.5 rounded-full border-2 border-signal/50"></span>
                    <span class="h-2 w-2 rounded-full bg-signal pulse-dot"></span>
                </span>
                <span class="truncate font-display text-lg font-semibold tracking-tight text-dawn">{{ config('app.name') }}</span>
            </a>
        </div>

        <nav class="flex-1 space-y-0.5 overflow-y-auto px-3 py-4">
            <a href="{{ route('dashboard') }}" class="{{ $navActive('dashboard') }}">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                Dashboard
            </a>
            <a href="{{ route('events.calendar') }}" class="{{ $navActive('events.calendar*') }}">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Calendar
            </a>
            <a href="{{ route('events.index') }}" class="{{ request()->routeIs('events.*') && ! request()->routeIs('events.calendar*') && ! request()->routeIs('events.messages.*') ? 'admin-nav-link admin-nav-link-active' : 'admin-nav-link' }}">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                Events
            </a>
            <a href="{{ route('analytics.index') }}" class="{{ $navActive('analytics.*') }}">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Analytics
            </a>

            @if(auth()->user()->belongsToOrganization())
                <x-admin.nav-heading>Organization</x-admin.nav-heading>
                <a href="{{ route('organization.profile.edit') }}" class="{{ $navActive('organization.profile*') }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    Profile
                </a>
                @if(auth()->user()->isOrganizationAdmin())
                <a href="{{ route('users.index') }}" class="{{ $navActive('users.*') }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    Users
                </a>
                @endif
                <a href="{{ route('subscription.plans') }}" class="{{ $navActive('subscription.*') }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    Plans
                </a>
                <a href="{{ route('usage.index') }}" class="{{ $navActive('usage.*') }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    Usage
                </a>
                <a href="{{ route('contacts.index') }}" class="{{ $navActive('contacts.*') }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    Contacts
                </a>

                <x-admin.nav-heading>Communications</x-admin.nav-heading>
                <a href="{{ route('messages.hub') }}" class="{{ request()->routeIs('messages.hub') || request()->routeIs('events.messages.*') ? 'admin-nav-link admin-nav-link-active' : 'admin-nav-link' }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Messages
                </a>
                <a href="{{ route('templates.index') }}" class="{{ $navActive('templates.*') }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6z"/></svg>
                    Templates
                </a>
                <a href="{{ route('email-senders.index') }}" class="{{ $navActive('email-senders.*') }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                    Email senders
                </a>
                <a href="{{ route('queue.monitor') }}" class="{{ $navActive('queue.*') }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Queue
                </a>
                <a href="{{ route('rsvp.dashboard') }}" class="{{ $navActive('rsvp.dashboard') }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    RSVP
                </a>
                <a href="{{ route('social.index') }}" class="{{ $navActive('social.*') }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                    Social
                </a>
                @can('viewAny', App\Models\BeepCall::class)
                <a href="{{ route('beep-calls.index') }}" class="{{ $navActive('beep-calls.*') }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    Beep Calls
                </a>
                @else
                <a href="{{ route('subscription.upgrade', ['plan' => App\Models\SubscriptionPlan::SLUG_PREMIUM]) }}" class="admin-nav-link opacity-75" title="Premium plan required">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    Beep Calls
                    <span class="ml-auto rounded bg-dawn/10 px-1.5 py-0.5 text-[10px] font-medium uppercase tracking-wide text-dawn/60">Premium</span>
                </a>
                @endcan
                <a href="{{ route('surveys.index') }}" class="{{ $navActive('surveys.*') }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    Surveys
                </a>
            @endif
        </nav>

        @if(auth()->user()->belongsToOrganization() && isset($usageMeter))
        <div class="border-t border-dawn/10 p-4">
            <p class="mb-2 font-mono text-[10px] uppercase tracking-widest text-dawn/45">Usage this month</p>
            <div class="space-y-2.5">
                <div>
                    <div class="mb-0.5 flex justify-between text-xs">
                        <span class="text-dawn/60">Events</span>
                        <span class="text-dawn/85">{{ $usageMeter['events_used'] ?? 0 }}/{{ $usageMeter['events_limit'] ?? '∞' }}</span>
                    </div>
                    <div class="h-1.5 overflow-hidden rounded-full bg-dusk-2">
                        <div class="h-full rounded-full transition-all {{ ($usageMeter['events_near_limit'] ?? false) ? 'bg-signal' : 'bg-coral' }}" style="width: {{ min(100, ($usageMeter['events_pct'] ?? 0)) }}%"></div>
                    </div>
                </div>
                <div>
                    <div class="mb-0.5 flex justify-between text-xs">
                        <span class="text-dawn/60">Contacts</span>
                        <span class="text-dawn/85">{{ $usageMeter['contacts_used'] ?? 0 }}/{{ $usageMeter['contacts_limit'] ?? '∞' }}</span>
                    </div>
                    <div class="h-1.5 overflow-hidden rounded-full bg-dusk-2">
                        <div class="h-full rounded-full transition-all {{ ($usageMeter['contacts_near_limit'] ?? false) ? 'bg-signal' : 'bg-sage' }}" style="width: {{ min(100, ($usageMeter['contacts_pct'] ?? 0)) }}%"></div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</aside>

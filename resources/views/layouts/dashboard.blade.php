<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700&family=jetbrains-mono:400,500" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-screen bg-slate-50 font-sans antialiased">
    <div class="flex min-h-screen">
        {{-- Sidebar --}}
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-64 bg-slate-900 text-slate-200 transform transition-transform duration-200 lg:translate-x-0 -translate-x-full">
            <div class="flex flex-col h-full">
                <div class="flex items-center gap-3 px-6 py-5 border-b border-slate-700/50">
                    <div class="w-9 h-9 rounded-lg bg-indigo-500 flex items-center justify-center font-bold text-white text-sm">{{ substr(config('app.name'), 0, 1) }}</div>
                    <span class="font-semibold text-white">{{ config('app.name') }}</span>
                </div>
                <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-0.5">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        Dashboard
                    </a>
                    <a href="{{ route('events.calendar') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('events.calendar*') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Calendar
                    </a>
                    <a href="{{ route('events.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('events.*') && !request()->routeIs('events.calendar*') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                        Events
                    </a>
                    <a href="{{ route('analytics.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('analytics.*') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        Analytics
                    </a>
                    @if(auth()->user()->belongsToOrganization())
                    <div class="pt-4 pb-2">
                        <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Organization</p>
                    </div>
                    <a href="{{ route('organization.profile.edit') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('organization.profile*') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        Profile
                    </a>
                    @if(auth()->user()->isOrganizationAdmin())
                    <a href="{{ route('users.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('users.*') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        Users
                    </a>
                    @endif
                    <a href="{{ route('subscription.plans') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('subscription.*') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        Plans
                    </a>
                    <a href="{{ route('usage.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('usage.*') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        Usage
                    </a>
                    <div class="pt-4 pb-2">
                        <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Communications</p>
                    </div>
                    <a href="{{ route('templates.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('templates.*') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6z"/></svg>
                        Templates
                    </a>
                    <a href="{{ route('queue.monitor') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('queue.*') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Queue
                    </a>
                    <a href="{{ route('rsvp.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('rsvp.dashboard') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        RSVP
                    </a>
                    <a href="{{ route('social.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('social.*') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                        Social
                    </a>
                    <a href="{{ route('beep-calls.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('beep-calls.*') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        Beep Calls
                    </a>
                    <a href="{{ route('surveys.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('surveys.*') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        Surveys
                    </a>
                    @endif
                </nav>
                @if(auth()->user()->belongsToOrganization() && isset($usageMeter))
                <div class="p-4 border-t border-slate-700/50">
                    <p class="text-xs font-medium text-slate-500 mb-2">Usage this month</p>
                    <div class="space-y-2">
                        <div>
                            <div class="flex justify-between text-xs mb-0.5">
                                <span class="text-slate-400">Events</span>
                                <span class="text-slate-300">{{ $usageMeter['events_used'] ?? 0 }}/{{ $usageMeter['events_limit'] ?? '∞' }}</span>
                            </div>
                            <div class="h-1.5 bg-slate-700 rounded-full overflow-hidden">
                                <div class="h-full bg-indigo-500 rounded-full transition-all" style="width: {{ min(100, ($usageMeter['events_pct'] ?? 0)) }}%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-xs mb-0.5">
                                <span class="text-slate-400">Contacts</span>
                                <span class="text-slate-300">{{ $usageMeter['contacts_used'] ?? 0 }}/{{ $usageMeter['contacts_limit'] ?? '∞' }}</span>
                            </div>
                            <div class="h-1.5 bg-slate-700 rounded-full overflow-hidden">
                                <div class="h-full bg-emerald-500 rounded-full transition-all" style="width: {{ min(100, ($usageMeter['contacts_pct'] ?? 0)) }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </aside>

        {{-- Overlay for mobile --}}
        <div id="sidebar-overlay" class="fixed inset-0 z-30 bg-slate-900/50 backdrop-blur-sm lg:hidden hidden" onclick="document.getElementById('sidebar').classList.add('-translate-x-full'); this.classList.add('hidden');"></div>

        {{-- Main content --}}
        <div class="flex-1 lg:pl-64">
            <header class="sticky top-0 z-20 bg-white/95 backdrop-blur border-b border-slate-200">
                <div class="flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8">
                    <button type="button" onclick="document.getElementById('sidebar').classList.toggle('-translate-x-full'); document.getElementById('sidebar-overlay').classList.toggle('hidden');" class="lg:hidden p-2 -ml-2 rounded-lg text-slate-600 hover:bg-slate-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <div class="flex-1 min-w-0"></div>
                    <div class="flex items-center gap-2 sm:gap-4">
                        {{-- Notification center --}}
                        @include('layouts.partials.notification-center')
                        <div class="hidden sm:block text-right">
                            <p class="text-sm font-medium text-slate-900">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-slate-500">{{ auth()->user()->organization?->name ?? 'System' }} · {{ ucfirst(auth()->user()->role) }}</p>
                        </div>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-sm text-slate-600 hover:text-slate-900 font-medium">Sign out</button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="p-4 sm:p-6 lg:p-8">
                @if(session('status'))
                    <div class="mb-6 p-4 rounded-xl bg-emerald-50 text-emerald-800 text-sm border border-emerald-200">{{ session('status') }}</div>
                @endif
                @if(session('error'))
                    <div class="mb-6 p-4 rounded-xl bg-red-50 text-red-800 text-sm border border-red-200">{{ session('error') }}</div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Super Admin') - {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300..700;1,9..144,300..700&family=IBM+Plex+Mono:wght@400;500&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak] { display: none !important; }</style>
    @stack('head')
</head>
<body class="min-h-screen bg-canvas font-sans text-ink antialiased">
    <div class="flex min-h-screen">
        @php
            $saNav = fn (string $pattern): string => request()->routeIs($pattern)
                ? 'admin-nav-link bg-amber-500 text-white shadow-sm hover:bg-amber-500 hover:text-white'
                : 'admin-nav-link';
        @endphp

        <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-64 -translate-x-full transform bg-dusk text-dawn transition-transform duration-200 lg:translate-x-0">
            <div class="flex h-full flex-col">
                <div class="flex items-center gap-3 border-b border-dawn/10 px-5 py-5">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-amber-500 text-sm font-bold text-white">SA</div>
                    <div class="min-w-0">
                        <span class="block truncate font-display text-lg font-semibold tracking-tight text-dawn">{{ config('app.name') }}</span>
                        <p class="text-xs text-amber-300/90">Super Admin</p>
                    </div>
                </div>
                <nav class="flex-1 space-y-0.5 overflow-y-auto px-3 py-4">
                    <a href="{{ route('super-admin.dashboard') }}" class="{{ $saNav('super-admin.dashboard') }}">
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        Dashboard
                    </a>
                    <a href="{{ route('super-admin.organizations') }}" class="{{ $saNav('super-admin.organizations*') }}">
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        Organizations
                    </a>
                    <a href="{{ route('super-admin.users') }}" class="{{ $saNav('super-admin.users*') }}">
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H4v-2a4 4 0 014-4h1m4-6a4 4 0 11-8 0 4 4 0 018 0zm6 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        Users
                    </a>
                    <a href="{{ route('super-admin.events') }}" class="{{ $saNav('super-admin.events*') }}">
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Events
                    </a>
                    <a href="{{ route('super-admin.activity') }}" class="{{ $saNav('super-admin.activity*') }}">
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Activity Logs
                    </a>
                    <a href="{{ route('super-admin.system-health') }}" class="{{ $saNav('super-admin.system-health*') }}">
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        System Health
                    </a>
                    <div class="mt-4 border-t border-dawn/10 pt-4">
                        <a href="{{ route('home') }}" class="admin-nav-link">
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                            Back to App
                        </a>
                    </div>
                </nav>
            </div>
        </aside>

        <div id="sidebar-overlay" class="fixed inset-0 z-30 hidden bg-dusk/60 backdrop-blur-sm lg:hidden" onclick="document.getElementById('sidebar').classList.add('-translate-x-full'); this.classList.add('hidden');"></div>

        <div class="flex min-w-0 flex-1 flex-col lg:pl-64">
            <header class="sticky top-0 z-20 border-b border-canvas-2/90 bg-canvas/95 backdrop-blur">
                <div class="flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8">
                    <button type="button" onclick="document.getElementById('sidebar').classList.toggle('-translate-x-full'); document.getElementById('sidebar-overlay').classList.toggle('hidden');" class="-ml-2 rounded-lg p-2 text-ink/70 hover:bg-canvas-2/80 lg:hidden">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <div class="min-w-0 flex-1"></div>
                    <div class="flex items-center gap-4">
                        <span class="rounded bg-amber-100 px-2 py-1 text-xs font-medium text-amber-800">Super Admin</span>
                        <p class="hidden text-sm font-medium text-ink sm:block">{{ auth()->user()->name }}</p>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-sm font-medium text-ink/70 transition hover:text-coral">Sign out</button>
                        </form>
                    </div>
                </div>
            </header>
            <main class="p-4 sm:p-6 lg:p-8">
                @if(session('status'))
                    <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('status') }}</div>
                @endif
                @if(session('error'))
                    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ session('error') }}</div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>

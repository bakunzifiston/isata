<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300..700;1,9..144,300..700&family=IBM+Plex+Mono:wght@400;500&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak] { display: none !important; }</style>
    @stack('head')
</head>
<body class="min-h-screen bg-canvas font-sans text-ink antialiased">
    <div class="flex min-h-screen">
        @include('layouts.partials.dashboard-sidebar')

        <div id="sidebar-overlay" class="fixed inset-0 z-30 hidden bg-dusk/60 backdrop-blur-sm lg:hidden" onclick="document.getElementById('sidebar').classList.add('-translate-x-full'); this.classList.add('hidden');"></div>

        <div class="flex min-w-0 flex-1 flex-col lg:pl-64">
            <header class="sticky top-0 z-20 border-b border-canvas-2/90 bg-canvas/95 backdrop-blur">
                <div class="flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8">
                    <button type="button" onclick="document.getElementById('sidebar').classList.toggle('-translate-x-full'); document.getElementById('sidebar-overlay').classList.toggle('hidden');" class="-ml-2 rounded-lg p-2 text-ink/70 hover:bg-canvas-2/80 lg:hidden">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <div class="min-w-0 flex-1"></div>
                    <div class="flex items-center gap-2 sm:gap-4">
                        @include('layouts.partials.notification-center')
                        <div class="hidden text-right sm:block">
                            <p class="text-sm font-medium text-ink">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-ink/55">{{ auth()->user()->organization?->name ?? 'System' }} · {{ ucfirst(auth()->user()->role) }}</p>
                        </div>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-sm font-medium text-ink/70 transition hover:text-coral">Sign out</button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                @if(session('status'))
                    <div class="mb-6 rounded-xl border border-sage/30 bg-sage/10 px-4 py-3 text-sm text-ink">{{ session('status') }}</div>
                @endif
                @if(session('error'))
                    <div class="mb-6 rounded-xl border border-coral/30 bg-coral/10 px-4 py-3 text-sm text-ink">{{ session('error') }}</div>
                @endif
                @if(!empty($usageWarnings))
                    @foreach($usageWarnings as $usageWarning)
                    <div class="mb-6 rounded-xl border border-signal/35 bg-signal/10 px-4 py-3 text-sm text-ink">{{ $usageWarning }}</div>
                    @endforeach
                @endif
                @yield('content')
            </main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>

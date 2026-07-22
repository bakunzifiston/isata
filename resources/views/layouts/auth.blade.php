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
</head>
<body class="min-h-screen bg-dawn font-sans text-ink antialiased">
    <div class="min-h-screen lg:grid lg:grid-cols-2">
        <aside class="relative hidden flex-col justify-between overflow-hidden bg-dusk p-10 text-dawn lg:flex xl:p-14">
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_bottom_left,_rgba(244,167,60,0.1)_0%,_transparent_60%)]"></div>

            <div class="relative">
                <x-marketing-logo variant="dark" />
            </div>

            <div class="relative max-w-md">
                <p class="font-mono text-xs uppercase tracking-widest text-signal">Offline-ready events</p>
                <h2 class="mt-4 font-display text-3xl font-semibold leading-tight tracking-tight xl:text-4xl">
                    Reach everyone. Even <em class="italic text-signal">offline</em>.
                </h2>
                <p class="mt-4 text-sm leading-relaxed text-dawn/70">
                    Email, SMS, branded beep calls, and social — one platform for organizers who can't rely on stable internet.
                </p>
                <x-pulse-divider variant="dusk" class="mt-10" />
            </div>

            <p class="relative font-mono text-xs text-dawn/45">© {{ date('Y') }} {{ config('app.name') }}</p>
        </aside>

        <main class="flex flex-col">
            <div class="flex items-center justify-between px-4 py-5 sm:px-8 lg:px-12">
                <a href="{{ route('home') }}" class="lg:hidden">
                    <x-marketing-logo />
                </a>
                <a href="{{ route('home') }}" class="ml-auto font-mono text-xs text-ink/50 transition hover:text-ink">
                    ← Back to home
                </a>
            </div>

            <div class="flex flex-1 flex-col items-center justify-center px-4 pb-12 sm:px-8 lg:px-12">
                <div class="w-full max-w-md">
                    @yield('content')
                </div>
            </div>
        </main>
    </div>
</body>
</html>

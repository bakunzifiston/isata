<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} — Event Management for Organizations</title>
    <meta name="description" content="Create events, manage attendees, and communicate through Email, SMS, Social Media, and Beep Calls. All-in-one event management platform.">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white font-sans antialiased text-slate-900">
    <header class="border-b border-slate-200">
        <div class="max-w-5xl mx-auto px-4 sm:px-6">
            <div class="flex justify-between items-center h-16">
                <a href="{{ url('/') }}" class="flex items-center gap-2.5">
                    <img src="{{ asset('images/isata-logo.svg') }}" alt="{{ config('app.name') }} logo" class="h-8 w-auto">
                </a>
                <nav class="flex items-center gap-4 sm:gap-6">
                    @auth
                        <a href="{{ route('dashboard') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900 transition-colors">Dashboard</a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-sm font-medium text-slate-600 hover:text-slate-900 transition-colors">Sign out</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900 transition-colors">Log in</a>
                        <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition-colors">
                            Get started
                        </a>
                    @endauth
                </nav>
            </div>
        </div>
    </header>

    <main>
        <section class="py-16 sm:py-24 lg:py-28">
            <div class="max-w-5xl mx-auto px-4 sm:px-6">
                <div class="max-w-2xl">
                    <p class="text-sm font-medium text-indigo-600">Event management platform</p>
                    <h1 class="mt-3 text-4xl sm:text-5xl font-bold tracking-tight leading-tight">
                        Manage events.
                        <span class="text-indigo-600">Engage attendees.</span>
                    </h1>
                    <p class="mt-5 text-lg text-slate-600 leading-relaxed">
                        Create events, manage attendees, and reach people through email, SMS, social media, and beep calls — all in one place.
                    </p>
                    @guest
                    <div class="mt-8 flex flex-col sm:flex-row gap-3">
                        <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-5 py-2.5 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition-colors">
                            Get started free
                        </a>
                        <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-5 py-2.5 rounded-lg border border-slate-300 text-slate-700 text-sm font-semibold hover:bg-slate-50 transition-colors">
                            Sign in
                        </a>
                    </div>
                    @else
                    <div class="mt-8">
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center px-5 py-2.5 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition-colors">
                            Go to dashboard
                        </a>
                    </div>
                    @endguest
                </div>
            </div>
        </section>

        <section class="border-t border-slate-200 bg-slate-50 py-16 sm:py-20">
            <div class="max-w-5xl mx-auto px-4 sm:px-6">
                <div class="mb-12 max-w-xl">
                    <h2 class="text-2xl sm:text-3xl font-bold tracking-tight">Everything you need</h2>
                    <p class="mt-3 text-slate-600">A complete toolkit for event organizers and marketing teams.</p>
                </div>
                <div class="grid sm:grid-cols-2 gap-6">
                    <article class="rounded-xl border border-slate-200 bg-white p-6">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <h3 class="mt-4 text-base font-semibold">Events & calendar</h3>
                        <p class="mt-2 text-sm text-slate-600 leading-relaxed">Create events, set schedules, and view everything in a unified calendar.</p>
                    </article>
                    <article class="rounded-xl border border-slate-200 bg-white p-6">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <h3 class="mt-4 text-base font-semibold">Attendee management</h3>
                        <p class="mt-2 text-sm text-slate-600 leading-relaxed">Import contacts, track RSVPs, and manage attendee lists with ease.</p>
                    </article>
                    <article class="rounded-xl border border-slate-200 bg-white p-6">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <h3 class="mt-4 text-base font-semibold">Multi-channel comms</h3>
                        <p class="mt-2 text-sm text-slate-600 leading-relaxed">Email, SMS, social media, and beep calls — reach attendees their way.</p>
                    </article>
                    <article class="rounded-xl border border-slate-200 bg-white p-6">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100 text-slate-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        </div>
                        <h3 class="mt-4 text-base font-semibold">Analytics & reports</h3>
                        <p class="mt-2 text-sm text-slate-600 leading-relaxed">Track delivery rates, RSVPs, attendance, and engagement metrics.</p>
                    </article>
                </div>
            </div>
        </section>

        @guest
        <section class="py-16 sm:py-20">
            <div class="max-w-5xl mx-auto px-4 sm:px-6">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-6 py-10 sm:px-10 sm:py-12 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-bold tracking-tight">Ready to get started?</h2>
                        <p class="mt-2 text-slate-600">Create a free account and run your next event on {{ config('app.name') }}.</p>
                    </div>
                    <a href="{{ route('register') }}" class="inline-flex shrink-0 items-center justify-center px-5 py-2.5 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition-colors">
                        Create free account
                    </a>
                </div>
            </div>
        </section>
        @endguest
    </main>

    <footer class="border-t border-slate-200 py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <a href="{{ url('/') }}" class="inline-flex items-center gap-2">
                    <img src="{{ asset('images/isata-logo.svg') }}" alt="{{ config('app.name') }} logo" class="h-7 w-auto">
                </a>
                <div class="flex items-center gap-6 text-sm text-slate-500">
                    @auth
                        <a href="{{ route('dashboard') }}" class="hover:text-slate-700 transition-colors">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="hover:text-slate-700 transition-colors">Log in</a>
                        <a href="{{ route('register') }}" class="hover:text-slate-700 transition-colors">Register</a>
                    @endauth
                </div>
            </div>
            <p class="mt-6 text-sm text-slate-400">© {{ date('Y') }} {{ config('app.name') }}</p>
        </div>
    </footer>
</body>
</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} — Event Management for Organizations</title>
    <meta name="description" content="Create events, manage attendees, and communicate through Email, SMS, Social Media, and Beep Calls. All-in-one event management platform.">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700&family=jetbrains-mono:400,500" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white font-sans antialiased">
    {{-- Header --}}
    <header class="fixed top-0 left-0 right-0 z-50 bg-white/80 backdrop-blur-md border-b border-slate-200/60">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16 lg:h-18">
                <a href="{{ url('/') }}" class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-lg bg-indigo-600 flex items-center justify-center font-bold text-white text-sm">{{ substr(config('app.name'), 0, 1) }}</div>
                    <span class="text-lg font-semibold text-slate-900">{{ config('app.name') }}</span>
                </a>
                <nav class="flex items-center gap-6">
                    @auth
                        <a href="{{ route('dashboard') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900 transition">Dashboard</a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-sm font-medium text-slate-600 hover:text-slate-900 transition">Sign out</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900 transition">Log in</a>
                        <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition shadow-sm shadow-indigo-500/20">
                            Get started
                        </a>
                    @endauth
                </nav>
            </div>
        </div>
    </header>

    <main>
        {{-- Hero --}}
        <section class="relative pt-24 pb-20 lg:pt-32 lg:pb-28 overflow-hidden">
            <div class="absolute inset-0 -z-10">
                <div class="absolute inset-0 bg-gradient-to-b from-indigo-50/80 via-white to-white"></div>
                <div class="absolute top-0 right-0 w-1/2 h-full bg-[radial-gradient(ellipse_at_top_right,_rgba(99,102,241,0.08)_0%,_transparent_70%)]"></div>
                <div class="absolute bottom-0 left-0 w-1/2 h-1/2 bg-[radial-gradient(ellipse_at_bottom_left,_rgba(99,102,241,0.05)_0%,_transparent_70%)]"></div>
            </div>
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl">
                    <p class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-indigo-100 text-indigo-700 text-sm font-medium mb-6">
                        <span class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></span>
                        Event management platform
                    </p>
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-slate-900 tracking-tight leading-[1.1]">
                        Manage events.
                        <span class="text-indigo-600">Engage attendees.</span>
                    </h1>
                    <p class="mt-6 text-lg sm:text-xl text-slate-600 leading-relaxed max-w-2xl">
                        Create events, manage attendees, and communicate through Email, SMS, Social Media, and Beep Calls. One platform for your entire event lifecycle.
                    </p>
                    @guest
                    <div class="mt-10 flex flex-col sm:flex-row gap-4">
                        <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-indigo-600 text-white font-semibold hover:bg-indigo-700 transition shadow-lg shadow-indigo-500/25">
                            Get started free
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </a>
                        <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-6 py-3.5 rounded-xl border-2 border-slate-200 text-slate-700 font-semibold hover:border-slate-300 hover:bg-slate-50 transition">
                            Sign in
                        </a>
                    </div>
                    @else
                    <div class="mt-10">
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-indigo-600 text-white font-semibold hover:bg-indigo-700 transition shadow-lg shadow-indigo-500/25">
                            Go to Dashboard
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </a>
                    </div>
                    @endguest
                </div>
            </div>
        </section>

        {{-- Features --}}
        <section class="py-20 lg:py-28 bg-slate-50/50">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-2xl mx-auto mb-16">
                    <h2 class="text-3xl sm:text-4xl font-bold text-slate-900 tracking-tight">Everything you need</h2>
                    <p class="mt-4 text-lg text-slate-600">A complete toolkit for event organizers and marketing teams</p>
                </div>
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8">
                    <div class="bg-white rounded-2xl p-6 lg:p-8 border border-slate-200/80 shadow-sm hover:shadow-md hover:border-indigo-200/60 transition-all">
                        <div class="w-12 h-12 rounded-xl bg-indigo-100 flex items-center justify-center mb-5">
                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900">Events & Calendar</h3>
                        <p class="mt-2 text-slate-600 text-sm leading-relaxed">Create events, set schedules, and view everything in a unified calendar.</p>
                    </div>
                    <div class="bg-white rounded-2xl p-6 lg:p-8 border border-slate-200/80 shadow-sm hover:shadow-md hover:border-indigo-200/60 transition-all">
                        <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center mb-5">
                            <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900">Attendee Management</h3>
                        <p class="mt-2 text-slate-600 text-sm leading-relaxed">Import contacts, track RSVPs, and manage attendee lists with ease.</p>
                    </div>
                    <div class="bg-white rounded-2xl p-6 lg:p-8 border border-slate-200/80 shadow-sm hover:shadow-md hover:border-indigo-200/60 transition-all">
                        <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center mb-5">
                            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900">Multi-Channel Comms</h3>
                        <p class="mt-2 text-slate-600 text-sm leading-relaxed">Email, SMS, Social Media, and Beep Calls — reach attendees their way.</p>
                    </div>
                    <div class="bg-white rounded-2xl p-6 lg:p-8 border border-slate-200/80 shadow-sm hover:shadow-md hover:border-indigo-200/60 transition-all">
                        <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center mb-5">
                            <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900">Analytics & Reports</h3>
                        <p class="mt-2 text-slate-600 text-sm leading-relaxed">Track delivery rates, RSVPs, attendance, and engagement metrics.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- CTA --}}
        <section class="py-20 lg:py-28">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl sm:text-4xl font-bold text-slate-900 tracking-tight">Ready to get started?</h2>
                <p class="mt-4 text-lg text-slate-600">Join organizations that trust {{ config('app.name') }} for their events.</p>
                @guest
                <div class="mt-10 flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-2 px-8 py-4 rounded-xl bg-indigo-600 text-white font-semibold hover:bg-indigo-700 transition shadow-lg shadow-indigo-500/25">
                        Create free account
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </a>
                </div>
                @endguest
            </div>
        </section>

        {{-- Footer --}}
        <footer class="border-t border-slate-200 bg-slate-50/50 py-12">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center font-bold text-white text-xs">{{ substr(config('app.name'), 0, 1) }}</div>
                        <span class="font-semibold text-slate-900">{{ config('app.name') }}</span>
                    </div>
                    <div class="flex items-center gap-8 text-sm text-slate-500">
                        @auth
                            <a href="{{ route('dashboard') }}" class="hover:text-slate-700 transition">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="hover:text-slate-700 transition">Log in</a>
                            <a href="{{ route('register') }}" class="hover:text-slate-700 transition">Register</a>
                        @endauth
                    </div>
                </div>
                <p class="mt-6 text-sm text-slate-400 text-center sm:text-left">© {{ date('Y') }} {{ config('app.name') }}. Event management platform.</p>
            </div>
        </footer>
    </main>
</body>
</html>

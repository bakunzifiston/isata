<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RSVP — {{ $event->name }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 font-sans antialiased text-slate-900">
    <div class="flex min-h-screen flex-col items-center justify-center px-4 py-12">
        <div class="w-full max-w-md">
            <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
                <p class="text-xs font-medium uppercase tracking-wide text-indigo-600">RSVP</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight">{{ $event->name }}</h1>

                <dl class="mt-4 space-y-2 text-sm text-slate-600">
                    <div>
                        <dt class="sr-only">Date</dt>
                        <dd>
                            {{ $event->date->format('l, F j, Y') }}
                            @if($event->time_formatted)
                                at {{ $event->time_formatted }}
                            @endif
                        </dd>
                    </div>
                    @if($event->effectiveFormat() === 'online' && $event->meeting_link)
                    <div>
                        <dt class="sr-only">Location</dt>
                        <dd>
                            <a href="{{ $event->meeting_link }}" target="_blank" rel="noopener" class="font-medium text-indigo-600 hover:text-indigo-700">
                                Join online
                            </a>
                        </dd>
                    </div>
                    @elseif($event->venue)
                    <div>
                        <dt class="sr-only">Venue</dt>
                        <dd>{{ $event->venue }}</dd>
                    </div>
                    @endif
                </dl>

                <div class="mt-8 border-t border-slate-100 pt-8">
                    <p class="text-sm text-slate-700">Enter the email address your invitation was sent to.</p>

                    @if($error ?? null)
                    <p class="mt-3 rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{{ $error }}</p>
                    @endif

                    <form method="GET" action="{{ route('rsvp.lookup') }}" class="mt-4 space-y-4">
                        <input type="hidden" name="event" value="{{ $event->id }}">
                        <div>
                            <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">Email</label>
                            <input type="email" name="email" id="email" placeholder="you@example.com" required value="{{ old('email') }}"
                                class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <button type="submit" class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 transition-colors">
                            Continue to RSVP
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

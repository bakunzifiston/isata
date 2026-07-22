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
                    <p class="text-sm text-slate-700">Hi {{ $attendee->name }}, will you be attending?</p>

                    <form method="POST" action="{{ \Illuminate\Support\Facades\URL::signedRoute('rsvp.store', ['event' => $event, 'attendee' => $attendee], \App\Support\SignedUrlTtl::rsvpExpiresAt()) }}" class="mt-5 space-y-2">
                        @csrf
                        <input type="hidden" name="response_channel" value="web">
                        <button type="submit" name="response" value="Yes" class="w-full rounded-lg bg-emerald-600 px-4 py-3 text-sm font-semibold text-white hover:bg-emerald-700 transition-colors">
                            Yes, I'll be there
                        </button>
                        <button type="submit" name="response" value="Maybe" class="w-full rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800 hover:bg-amber-100 transition-colors">
                            Maybe
                        </button>
                        <button type="submit" name="response" value="No" class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                            No, I can't make it
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

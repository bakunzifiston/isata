<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RSVP - {{ $event->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 font-sans antialiased flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-lg border border-slate-200 max-w-md w-full p-8">
        <h1 class="text-xl font-bold text-slate-900">{{ $event->name }}</h1>
        <p class="mt-2 text-slate-600">
            {{ $event->date->format('l, F j, Y') }}
            @if($event->time_formatted)
                at {{ $event->time_formatted }}
            @endif
        </p>
        @if($event->venue)
        <p class="mt-1 text-sm text-slate-500">{{ $event->venue }}</p>
        @endif

        <p class="mt-6 text-slate-700">Hi {{ $attendee->name }}, will you be attending?</p>

        <form method="POST" action="{{ \Illuminate\Support\Facades\URL::signedRoute('rsvp.store', ['event' => $event, 'attendee' => $attendee], now()->addDays(30)) }}" class="mt-6 space-y-3">
            @csrf
            <input type="hidden" name="response_channel" value="web">
            <div class="flex flex-col gap-2">
                <button type="submit" name="response" value="Yes" class="w-full px-4 py-3 rounded-lg bg-emerald-600 text-white font-medium hover:bg-emerald-700">
                    Yes, I'll be there
                </button>
                <button type="submit" name="response" value="Maybe" class="w-full px-4 py-3 rounded-lg bg-amber-500 text-white font-medium hover:bg-amber-600">
                    Maybe
                </button>
                <button type="submit" name="response" value="No" class="w-full px-4 py-3 rounded-lg bg-slate-600 text-white font-medium hover:bg-slate-700">
                    No, I can't make it
                </button>
            </div>
        </form>
    </div>
</body>
</html>

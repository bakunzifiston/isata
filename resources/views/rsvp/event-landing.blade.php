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

        <p class="mt-6 text-slate-700">Received an invitation? Enter your email to RSVP.</p>

        @if($error ?? null)
        <p class="mt-2 text-sm text-red-600">{{ $error }}</p>
        @endif

        <form method="GET" action="{{ route('rsvp.lookup') }}" class="mt-4">
            <input type="hidden" name="event" value="{{ $event->id }}">
            <input type="email" name="email" placeholder="Your email" required value="{{ old('email') }}"
                class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 mb-3">
            <p class="text-xs text-slate-500 mb-3">We'll look up your invitation and take you to RSVP.</p>
            <button type="submit" class="w-full px-4 py-3 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-700">
                Get my RSVP link
            </button>
        </form>
    </div>

</body>
</html>

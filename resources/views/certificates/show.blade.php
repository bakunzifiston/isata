<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Certificate - {{ $event->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .certificate { min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 2rem; border: 8px double #6366f1; margin: 2rem; }
        .certificate h1 { font-size: 2rem; color: #1e293b; margin-bottom: 0.5rem; }
        .certificate .subtitle { color: #64748b; font-size: 1rem; }
        .certificate .name { font-size: 2.5rem; font-weight: bold; color: #6366f1; margin: 1.5rem 0; }
        .certificate .event { font-size: 1.25rem; color: #475569; }
        .certificate .date { margin-top: 2rem; font-size: 0.875rem; color: #94a3b8; }
    </style>
</head>
<body class="bg-white">
    <div class="certificate">
        <h1>Certificate of Participation</h1>
        <p class="subtitle">This certifies that</p>
        <p class="name">{{ $attendee->name }}</p>
        <p class="event">participated in {{ $event->name }}</p>
        <p class="date">{{ $event->date->format('F j, Y') }}</p>
    </div>
    <p class="text-center text-sm text-slate-500 mb-4">Print this page (Ctrl+P / Cmd+P) to save as PDF</p>
</body>
</html>

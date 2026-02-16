<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Thank you</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 font-sans antialiased flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-lg border border-slate-200 max-w-md w-full p-8 text-center">
        <div class="w-16 h-16 rounded-full bg-emerald-100 flex items-center justify-center mx-auto mb-6">
            <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h1 class="text-xl font-bold text-slate-900">Thank you!</h1>
        <p class="mt-2 text-slate-600">{{ session('status', 'Your response has been recorded.') }}</p>
    </div>
</body>
</html>

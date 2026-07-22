@props(['variant' => 'light'])

@php
    $textColor = $variant === 'dark' ? 'text-ink' : 'text-dawn';
    $ringColor = $variant === 'dark' ? 'border-ink/20' : 'border-dawn/30';
@endphp

<a {{ $attributes->merge(['class' => 'inline-flex items-center gap-3 group']) }} href="{{ url('/') }}">
    <span class="relative flex h-9 w-9 items-center justify-center">
        <span class="absolute inset-0 rounded-full border-2 {{ $ringColor }}"></span>
        <span class="absolute inset-1.5 rounded-full border-2 border-signal/60"></span>
        <span class="h-2 w-2 rounded-full bg-signal pulse-dot"></span>
    </span>
    <span class="font-display text-lg font-semibold tracking-tight {{ $textColor }}">{{ config('app.name') }}</span>
</a>

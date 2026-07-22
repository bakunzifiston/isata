@props(['variant' => 'dawn'])

@php
    $stroke = $variant === 'dusk' ? 'rgba(244, 167, 60, 0.35)' : 'rgba(33, 28, 28, 0.12)';
@endphp

<div {{ $attributes->merge(['class' => 'w-full']) }} aria-hidden="true">
    <svg class="w-full h-3" preserveAspectRatio="none" viewBox="0 0 1200 12" fill="none" xmlns="http://www.w3.org/2000/svg">
        <line x1="0" y1="6" x2="1200" y2="6" stroke="{{ $stroke }}" stroke-width="1.5" stroke-dasharray="4 7" />
    </svg>
</div>

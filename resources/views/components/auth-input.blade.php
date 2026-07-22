@props([
    'label',
    'name',
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'autofocus' => false,
    'autocomplete' => null,
])

<div>
    <label for="{{ $name }}" class="mb-1.5 block text-sm font-medium text-ink">{{ $label }}</label>
    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $name }}"
        @if($value !== null) value="{{ $value }}" @endif
        @if($placeholder) placeholder="{{ $placeholder }}" @endif
        @if($required) required @endif
        @if($autofocus) autofocus @endif
        @if($autocomplete) autocomplete="{{ $autocomplete }}" @endif
        {{ $attributes->merge(['class' => 'w-full rounded-xl border border-ink/15 bg-white px-4 py-2.5 text-ink placeholder-ink/35 transition focus:border-transparent focus:outline-none focus:ring-2 focus:ring-signal']) }}
    >
</div>

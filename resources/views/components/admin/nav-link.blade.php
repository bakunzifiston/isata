@props([
    'href',
    'active' => false,
])

<a href="{{ $href }}" @class([
    'admin-nav-link',
    'admin-nav-link-active' => $active,
])>
    @if(isset($icon))
        <span class="shrink-0 [&>svg]:h-5 [&>svg]:w-5">{{ $icon }}</span>
    @endif
    <span>{{ $slot }}</span>
</a>

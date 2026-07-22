@php
    $classes = [
        'pending' => 'bg-amber-100 text-amber-800',
        'confirmed' => 'bg-emerald-100 text-emerald-800',
        'declined' => 'bg-red-100 text-red-800',
        'attended' => 'bg-coral/15 text-coral',
    ];
    $class = $classes[$status] ?? 'bg-dawn-2 text-ink/70';
@endphp
<span class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $class }}">
    {{ \App\Models\Attendee::rsvpStatuses()[$status] ?? ucfirst($status) }}
</span>

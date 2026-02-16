@php
    $classes = [
        'pending' => 'bg-amber-100 text-amber-800',
        'confirmed' => 'bg-emerald-100 text-emerald-800',
        'declined' => 'bg-red-100 text-red-800',
        'attended' => 'bg-indigo-100 text-indigo-800',
    ];
    $class = $classes[$status] ?? 'bg-slate-100 text-slate-800';
@endphp
<span class="inline-flex px-2 py-1 text-xs font-medium rounded-full {{ $class }}">
    {{ \App\Models\Attendee::rsvpStatuses()[$status] ?? ucfirst($status) }}
</span>

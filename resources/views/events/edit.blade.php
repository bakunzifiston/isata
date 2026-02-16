@extends('layouts.dashboard')

@section('title', 'Edit Event - ' . config('app.name'))

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-900">Edit event</h1>
    <p class="mt-1 text-slate-600">Update event details</p>
</div>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 max-w-2xl">
    @include('events._form', [
        'event' => $event,
        'route' => route('events.update', $event),
        'method' => 'PUT',
        'templates' => $templates ?? collect(),
        'showReminders' => $event->exists,
    ])
</div>
@endsection

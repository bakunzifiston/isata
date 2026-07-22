@extends('layouts.dashboard')

@section('title', 'Edit Event - ' . config('app.name'))

@section('content')
<div class="mb-8">
    <h1 class="admin-page-title">Edit event</h1>
    <p class="admin-page-subtitle">Update event details</p>
</div>

<x-admin.card class="max-w-2xl">
    @include('events._form', [
        'event' => $event,
        'route' => route('events.update', $event),
        'method' => 'PUT',
        'templates' => $templates ?? collect(),
        'showReminders' => $event->exists,
    ])
</x-admin.card>
@endsection

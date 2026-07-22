@extends('layouts.dashboard')

@section('title', 'Create Event - ' . config('app.name'))

@section('content')
<div class="mb-8">
    <h1 class="admin-page-title">Create event</h1>
    <p class="admin-page-subtitle">Add a new event. Save as draft for offline editing later.</p>
</div>

<x-admin.card class="max-w-2xl">
    @include('events._form', ['event' => $event, 'route' => route('events.store'), 'method' => 'POST'])
</x-admin.card>
@endsection

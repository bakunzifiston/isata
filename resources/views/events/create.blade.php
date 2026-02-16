@extends('layouts.dashboard')

@section('title', 'Create Event - ' . config('app.name'))

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-900">Create event</h1>
    <p class="mt-1 text-slate-600">Add a new event. Save as draft for offline editing later.</p>
</div>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 max-w-2xl">
    @include('events._form', ['event' => $event, 'route' => route('events.store'), 'method' => 'POST'])
</div>
@endsection

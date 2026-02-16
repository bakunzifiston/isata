@extends('layouts.dashboard')

@section('title', 'Create Survey - ' . config('app.name'))

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-900">Create survey</h1>
    <p class="mt-1 text-slate-600">Build a feedback survey for your event</p>
</div>

<div class="max-w-2xl">
    @include('surveys._form', [
        'survey' => new \App\Models\Survey(),
        'events' => $events,
        'route' => route('surveys.store'),
        'method' => 'POST',
        'preselectedEventId' => $preselectedEventId ?? null,
    ])
</div>
@endsection

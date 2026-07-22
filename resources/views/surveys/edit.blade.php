@extends('layouts.dashboard')

@section('title', 'Edit Survey - ' . config('app.name'))

@section('content')
<div class="mb-8">
    <h1 class="admin-page-title">Edit survey</h1>
</div>

<div class="max-w-2xl">
    @include('surveys._form', [
        'survey' => $survey,
        'events' => $events,
        'route' => route('surveys.update', $survey),
        'method' => 'PUT',
        'preselectedEventId' => null,
    ])
</div>
@endsection

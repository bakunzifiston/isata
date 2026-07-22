@extends('layouts.dashboard')

@section('title', 'Create Template - ' . config('app.name'))

@section('content')
<div class="mb-8">
    <h1 class="admin-page-title">Create template</h1>
    <p class="admin-page-subtitle">Define a reusable message template with personalization tags</p>
</div>

<div class="max-w-2xl">
    @include('templates._form', [
        'template' => new \App\Models\MessageTemplate(),
        'channels' => $channels,
        'route' => route('templates.store'),
        'method' => 'POST',
    ])
</div>
@endsection

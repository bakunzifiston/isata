@extends('layouts.dashboard')

@section('title', 'Create Template - ' . config('app.name'))

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-900">Create template</h1>
    <p class="mt-1 text-slate-600">Define a reusable message template with personalization tags</p>
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

@extends('layouts.dashboard')

@section('title', 'Edit Template - ' . config('app.name'))

@section('content')
<div class="mb-8">
    <h1 class="admin-page-title">Edit template</h1>
</div>

<div class="max-w-2xl">
    @include('templates._form', [
        'template' => $template,
        'channels' => $channels,
        'route' => route('templates.update', $template),
        'method' => 'PUT',
    ])
</div>
@endsection

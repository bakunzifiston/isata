@extends('layouts.dashboard')

@section('title', 'Edit Social Post - ' . config('app.name'))

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-900">Edit social post</h1>
</div>

<div class="max-w-2xl">
    @include('social._form', [
        'post' => $post,
        'events' => $events,
        'accounts' => $accounts,
        'route' => route('social.update', $post),
        'method' => 'PUT',
    ])
</div>
@endsection

@extends('layouts.dashboard')

@section('title', 'Create Social Post - ' . config('app.name'))

@section('content')
<div class="mb-8">
    <h1 class="admin-page-title">Create social post</h1>
    <p class="admin-page-subtitle">Compose and schedule a post for social media</p>
</div>

<div class="max-w-2xl">
    @include('social._form', [
        'post' => $post,
        'events' => $events,
        'accounts' => $accounts,
        'route' => route('social.store'),
        'method' => 'POST',
    ])
</div>
@endsection

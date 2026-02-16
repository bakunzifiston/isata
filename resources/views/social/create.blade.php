@extends('layouts.dashboard')

@section('title', 'Create Social Post - ' . config('app.name'))

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-900">Create social post</h1>
    <p class="mt-1 text-slate-600">Compose and schedule a post for social media</p>
</div>

<div class="max-w-2xl">
    @include('social._form', [
        'post' => new \App\Models\SocialPost(['event_id' => $preselectedEventId ?? null, 'platform' => 'facebook']),
        'events' => $events,
        'accounts' => $accounts,
        'route' => route('social.store'),
        'method' => 'POST',
    ])
</div>
@endsection

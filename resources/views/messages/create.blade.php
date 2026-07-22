@extends('layouts.dashboard')

@section('title', 'Create Message - ' . config('app.name'))

@section('content')
<div class="mb-8">
    <a href="{{ route('events.show', $event) }}" class="admin-link-muted">← {{ $event->name }}</a>
    <h1 class="admin-page-title mt-2">Create message</h1>
    <p class="admin-page-subtitle">Build and schedule a message for your event</p>
</div>

<div class="max-w-4xl">
    @include('messages._form', [
        'event' => $event,
        'message' => new \App\Models\Message(),
        'channels' => $channels,
        'templates' => $templates,
        'senderIdentities' => $senderIdentities,
        'route' => route('events.messages.store', $event),
        'method' => 'POST',
    ])
</div>
@endsection

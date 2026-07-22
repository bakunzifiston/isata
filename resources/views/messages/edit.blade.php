@extends('layouts.dashboard')

@section('title', 'Edit Message - ' . config('app.name'))

@section('content')
<div class="mb-8">
    <a href="{{ route('events.show', $event) }}" class="admin-link-muted">← {{ $event->name }}</a>
    <h1 class="admin-page-title mt-2">Edit message</h1>
</div>

<div class="max-w-4xl">
    @include('messages._form', [
        'event' => $event,
        'message' => $message,
        'channels' => $channels,
        'templates' => $templates,
        'senderIdentities' => $senderIdentities,
        'route' => route('events.messages.update', [$event, $message]),
        'method' => 'PUT',
    ])
</div>
@endsection

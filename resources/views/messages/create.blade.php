@extends('layouts.dashboard')

@section('title', 'Create Message - ' . config('app.name'))

@section('content')
<div class="mb-8">
    <a href="{{ route('events.show', $event) }}" class="text-slate-600 hover:text-slate-900">← {{ $event->name }}</a>
    <h1 class="text-2xl font-bold text-slate-900 mt-2">Create message</h1>
    <p class="mt-1 text-slate-600">Build and schedule a message for your event</p>
</div>

<div class="max-w-2xl">
    @include('messages._form', [
        'event' => $event,
        'message' => new \App\Models\Message(),
        'channels' => $channels,
        'templates' => $templates,
        'route' => route('events.messages.store', $event),
        'method' => 'POST',
    ])
</div>
@endsection

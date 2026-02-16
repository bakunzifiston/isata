@extends('layouts.dashboard')

@section('title', 'Edit Message - ' . config('app.name'))

@section('content')
<div class="mb-8">
    <a href="{{ route('events.show', $event) }}" class="text-slate-600 hover:text-slate-900">← {{ $event->name }}</a>
    <h1 class="text-2xl font-bold text-slate-900 mt-2">Edit message</h1>
</div>

<div class="max-w-2xl">
    @include('messages._form', [
        'event' => $event,
        'message' => $message,
        'channels' => $channels,
        'templates' => $templates,
        'route' => route('events.messages.update', [$event, $message]),
        'method' => 'PUT',
    ])
</div>
@endsection

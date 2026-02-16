@extends('layouts.dashboard')

@section('title', $event->name . ' - ' . config('app.name'))

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
    <div>
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold text-slate-900">{{ $event->name }}</h1>
            <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full
                {{ $event->status === 'draft' ? 'bg-amber-100 text-amber-800' : '' }}
                {{ $event->status === 'scheduled' ? 'bg-emerald-100 text-emerald-800' : '' }}
                {{ $event->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}
                {{ $event->status === 'completed' ? 'bg-slate-100 text-slate-800' : '' }}
            ">
                {{ ucfirst($event->status) }}
            </span>
        </div>
        <p class="mt-1 text-slate-600">
            {{ $event->date->format('l, F j, Y') }}
            @if($event->time_formatted)
                at {{ $event->time_formatted }}
            @endif
        </p>
    </div>
    <div class="flex gap-2 flex-wrap">
        <a href="{{ route('events.attendees.index', $event) }}" class="px-4 py-2 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-700">
            Manage attendees ({{ $event->attendees()->count() }})
        </a>
        <a href="{{ route('events.messages.index', $event) }}" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 font-medium hover:bg-slate-50">
            Messages
        </a>
        <a href="{{ route('rsvp.dashboard', ['event_id' => $event->id]) }}" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 font-medium hover:bg-slate-50">
            RSVP
        </a>
        <a href="{{ route('analytics.event-report', $event) }}" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 font-medium hover:bg-slate-50">
            Analytics
        </a>
        <a href="{{ route('social.create') }}?event_id={{ $event->id }}" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 font-medium hover:bg-slate-50">
            Post to social
        </a>
        @if($event->organization?->subscriptionPlan?->hasBeepCalls())
        <a href="{{ route('beep-calls.create') }}?event_id={{ $event->id }}" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 font-medium hover:bg-slate-50">
            Beep call
        </a>
        @endif
        <a href="{{ route('surveys.create') }}?event_id={{ $event->id }}" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 font-medium hover:bg-slate-50">
            Survey
        </a>
        <a href="{{ route('events.edit', $event) }}" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 font-medium hover:bg-slate-50">
            Edit
        </a>
        <a href="{{ route('events.index') }}" class="px-4 py-2 rounded-lg text-slate-600 hover:text-slate-900">
            ← Back to list
        </a>
    </div>
</div>

<div class="max-w-2xl space-y-6">
    @if($event->description)
    <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
        <h2 class="text-sm font-medium text-slate-500 uppercase mb-2">Description</h2>
        <p class="text-slate-700 whitespace-pre-wrap">{{ $event->description }}</p>
    </div>
    @endif

    <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
        <h2 class="text-sm font-medium text-slate-500 uppercase mb-4">Details</h2>
        <dl class="space-y-4">
            <div>
                <dt class="text-sm font-medium text-slate-500">Date & time</dt>
                <dd class="mt-1 text-slate-900">{{ $event->date->format('l, F j, Y') }}@if($event->time_formatted) at {{ $event->time_formatted }}@endif</dd>
            </div>
            @if($event->venue)
            <div>
                <dt class="text-sm font-medium text-slate-500">Venue</dt>
                <dd class="mt-1 text-slate-900">{{ $event->venue }}</dd>
            </div>
            @endif
            @if($event->meeting_link)
            <div>
                <dt class="text-sm font-medium text-slate-500">Meeting link</dt>
                <dd class="mt-1">
                    <a href="{{ $event->meeting_link }}" target="_blank" rel="noopener" class="text-indigo-600 hover:text-indigo-900">Join meeting →</a>
                </dd>
            </div>
            @endif
            @if($event->creator)
            <div>
                <dt class="text-sm font-medium text-slate-500">Created by</dt>
                <dd class="mt-1 text-slate-900">{{ $event->creator->name }}</dd>
            </div>
            @endif
        </dl>
    </div>
</div>
@endsection

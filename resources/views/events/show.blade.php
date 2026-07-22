@extends('layouts.dashboard')

@section('title', $event->name . ' - ' . config('app.name'))

@section('content')
@php
    $attendeeCount = $event->attendees()->count();
    $messageCount = $event->messages()->count();
    $isOnline = $event->effectiveFormat() === 'online';
    $statusClass = match ($event->status) {
        'draft' => 'bg-amber-100 text-amber-800',
        'scheduled' => 'bg-emerald-100 text-emerald-800',
        'cancelled' => 'bg-red-100 text-red-800',
        'completed' => 'bg-dawn-2 text-ink/70',
        default => 'bg-dawn-2 text-ink/70',
    };

    $workspaceLinks = [
        ['route' => route('events.attendees.index', $event), 'label' => 'Attendees', 'count' => $attendeeCount, 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
        ['route' => route('events.messages.index', $event), 'label' => 'Messages', 'count' => $messageCount, 'icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
        ['route' => route('rsvp.dashboard', ['event_id' => $event->id]), 'label' => 'RSVP', 'count' => null, 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['route' => route('analytics.event-report', $event), 'label' => 'Analytics', 'count' => null, 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
    ];

    $extraLinks = [
        ['route' => route('social.create') . '?event_id=' . $event->id, 'label' => 'Post to social', 'icon' => 'M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z'],
        ['route' => route('surveys.create') . '?event_id=' . $event->id, 'label' => 'Create survey', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
    ];

    if ($event->organization?->subscriptionPlan?->hasBeepCalls()) {
        $extraLinks[] = ['route' => route('beep-calls.create') . '?event_id=' . $event->id, 'label' => 'Schedule beep call', 'icon' => 'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z'];
    }
@endphp

<div class="lg:grid lg:grid-cols-3 lg:gap-8 lg:items-start">
    {{-- Event details (main) --}}
    <div class="min-w-0 lg:col-span-2">
        <div class="admin-card p-6 sm:p-8">
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="font-display text-2xl font-semibold tracking-tight text-ink sm:text-3xl">{{ $event->name }}</h1>
                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $statusClass }}">
                    {{ ucfirst($event->status) }}
                </span>
            </div>

            <dl class="mt-6 space-y-4 border-t border-dawn-2/60 pt-6 text-sm">
                <div class="flex gap-3">
                    <dt class="w-28 shrink-0 font-medium text-ink/50">When</dt>
                    <dd class="text-ink/80">
                        {{ $event->date->format('l, F j, Y') }}
                        @if($event->time_formatted)
                            <span class="text-ink/50">at</span> {{ $event->time_formatted }}
                        @endif
                    </dd>
                </div>
                <div class="flex gap-3">
                    <dt class="w-28 shrink-0 font-medium text-ink/50">Format</dt>
                    <dd class="text-ink/80">{{ $isOnline ? 'Online' : 'In person' }}</dd>
                </div>
                @if(!$isOnline && $event->venue)
                <div class="flex gap-3">
                    <dt class="w-28 shrink-0 font-medium text-ink/50">Location</dt>
                    <dd class="text-ink/80">{{ $event->venue }}</dd>
                </div>
                @endif
                @if($isOnline && $event->meeting_link)
                <div class="flex gap-3">
                    <dt class="w-28 shrink-0 font-medium text-ink/50">Meeting</dt>
                    <dd>
                        <a href="{{ $event->meeting_link }}" target="_blank" rel="noopener" class="admin-link break-all">Join meeting</a>
                    </dd>
                </div>
                @endif
                @if($event->creator)
                <div class="flex gap-3">
                    <dt class="w-28 shrink-0 font-medium text-ink/50">Created by</dt>
                    <dd class="text-ink/80">{{ $event->creator->name }}</dd>
                </div>
                @endif
                @if($event->description)
                <div class="flex gap-3">
                    <dt class="w-28 shrink-0 font-medium text-ink/50">About</dt>
                    <dd class="whitespace-pre-wrap leading-relaxed text-ink/80">{{ $event->description }}</dd>
                </div>
                @endif
            </dl>
        </div>
    </div>

    {{-- Side navigation --}}
    <aside class="mt-8 lg:mt-0">
        <div class="admin-card lg:sticky lg:top-24">
            <div class="border-b border-dawn-2/60 px-4 py-3">
                <p class="font-mono text-xs uppercase tracking-wider text-ink/50">Event menu</p>
            </div>

            <nav class="p-2">
                @foreach($workspaceLinks as $link)
                <a href="{{ $link['route'] }}" class="flex items-center justify-between rounded-lg px-3 py-2.5 text-sm font-medium text-ink/75 transition hover:bg-canvas hover:text-ink">
                    <span class="flex items-center gap-2.5">
                        <svg class="h-4 w-4 shrink-0 text-ink/40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $link['icon'] }}"/></svg>
                        {{ $link['label'] }}
                    </span>
                    @if($link['count'] !== null)
                    <span class="rounded-full bg-canvas px-2 py-0.5 text-xs font-medium text-ink/55">{{ $link['count'] }}</span>
                    @endif
                </a>
                @endforeach
            </nav>

            <div class="mx-4 border-t border-dawn-2/60"></div>

            <nav class="p-2">
                @foreach($extraLinks as $link)
                <a href="{{ $link['route'] }}" class="flex items-center gap-2.5 rounded-lg px-3 py-2.5 text-sm text-ink/65 transition hover:bg-canvas hover:text-ink">
                    <svg class="h-4 w-4 shrink-0 text-ink/35" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $link['icon'] }}"/></svg>
                    {{ $link['label'] }}
                </a>
                @endforeach
            </nav>

            <div class="mx-4 border-t border-dawn-2/60"></div>

            <div class="space-y-1 p-2">
                <a href="{{ route('events.edit', $event) }}" class="flex items-center gap-2.5 rounded-lg px-3 py-2.5 text-sm font-medium text-ink/75 transition hover:bg-canvas hover:text-ink">
                    <svg class="h-4 w-4 text-ink/40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit event
                </a>
                <a href="{{ route('events.index') }}" class="flex items-center gap-2.5 rounded-lg px-3 py-2.5 text-sm text-ink/55 transition hover:bg-canvas hover:text-ink">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    All events
                </a>
            </div>
        </div>
    </aside>
</div>
@endsection

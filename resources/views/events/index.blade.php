@extends('layouts.dashboard')

@section('title', 'Events - ' . config('app.name'))

@section('content')
<div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="admin-page-title">Events</h1>
        <p class="admin-page-subtitle">Manage your organization's events</p>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('events.calendar') }}" class="admin-btn-secondary">
            Calendar view
        </a>
        <a href="{{ route('events.create') }}" class="admin-btn-primary">
            Create event
        </a>
    </div>
</div>

<div class="mb-4 flex gap-2">
    <a href="{{ route('events.index') }}" class="{{ !request('status') ? 'admin-filter-active' : 'admin-filter' }}">
        All
    </a>
    <a href="{{ route('events.index', ['status' => 'draft']) }}" class="px-3 py-1.5 rounded-lg text-sm font-medium {{ request('status') === 'draft' ? 'bg-amber-100 text-amber-800' : 'admin-filter' }}">
        Drafts
    </a>
    <a href="{{ route('events.index', ['status' => 'scheduled']) }}" class="px-3 py-1.5 rounded-lg text-sm font-medium {{ request('status') === 'scheduled' ? 'bg-emerald-100 text-emerald-800' : 'admin-filter' }}">
        Scheduled
    </a>
</div>

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Event</th>
                <th>Date & Time</th>
                <th>Location</th>
                <th>Status</th>
                <th class="text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($events as $event)
            <tr>
                <td>
                    <a href="{{ route('events.show', $event) }}" class="admin-link">{{ $event->name }}</a>
                </td>
                <td>
                    {{ $event->date->format('M j, Y') }}
                    @if($event->time_formatted)
                        · {{ $event->time_formatted }}
                    @endif
                </td>
                <td>
                    @if($event->effectiveFormat() === 'online')
                        <span class="text-ink/50">Online</span>
                    @else
                        {{ $event->venue ?: '—' }}
                    @endif
                </td>
                <td>
                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium
                        {{ $event->status === 'draft' ? 'bg-amber-100 text-amber-800' : '' }}
                        {{ $event->status === 'scheduled' ? 'bg-emerald-100 text-emerald-800' : '' }}
                        {{ $event->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}
                        {{ $event->status === 'completed' ? 'bg-dawn-2 text-ink/70' : '' }}
                    ">
                        {{ ucfirst($event->status) }}
                    </span>
                </td>
                <td class="text-right">
                    <a href="{{ route('events.show', $event) }}" class="admin-link">View</a>
                    <a href="{{ route('events.edit', $event) }}" class="admin-link-muted ml-4">Edit</a>
                    <form method="POST" action="{{ route('events.destroy', $event) }}" class="inline ml-4" onsubmit="return confirm('Delete this event?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="py-12 text-center text-ink/50">No events yet. <a href="{{ route('events.create') }}" class="admin-link">Create your first event</a></td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($events->hasPages())
    <div class="border-t border-dawn-2/80 px-6 py-4">
        {{ $events->links() }}
    </div>
    @endif
</div>
@endsection

@extends('layouts.dashboard')

@section('title', 'Events - ' . config('app.name'))

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Events</h1>
        <p class="mt-1 text-slate-600">Manage your organization's events</p>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('events.calendar') }}" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 font-medium hover:bg-slate-50">
            Calendar view
        </a>
        <a href="{{ route('events.create') }}" class="px-4 py-2 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-700">
            Create event
        </a>
    </div>
</div>

<div class="mb-4 flex gap-2">
    <a href="{{ route('events.index') }}" class="px-3 py-1.5 rounded-lg text-sm font-medium {{ !request('status') ? 'bg-indigo-100 text-indigo-800' : 'text-slate-600 hover:bg-slate-100' }}">
        All
    </a>
    <a href="{{ route('events.index', ['status' => 'draft']) }}" class="px-3 py-1.5 rounded-lg text-sm font-medium {{ request('status') === 'draft' ? 'bg-amber-100 text-amber-800' : 'text-slate-600 hover:bg-slate-100' }}">
        Drafts
    </a>
    <a href="{{ route('events.index', ['status' => 'scheduled']) }}" class="px-3 py-1.5 rounded-lg text-sm font-medium {{ request('status') === 'scheduled' ? 'bg-emerald-100 text-emerald-800' : 'text-slate-600 hover:bg-slate-100' }}">
        Scheduled
    </a>
</div>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <table class="min-w-full divide-y divide-slate-200">
        <thead class="bg-slate-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Event</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Date & Time</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Venue</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            @forelse($events as $event)
            <tr class="hover:bg-slate-50">
                <td class="px-6 py-4">
                    <a href="{{ route('events.show', $event) }}" class="font-medium text-indigo-600 hover:text-indigo-900">{{ $event->name }}</a>
                </td>
                <td class="px-6 py-4 text-sm text-slate-600">
                    {{ $event->date->format('M j, Y') }}
                    @if($event->time_formatted)
                        · {{ $event->time_formatted }}
                    @endif
                </td>
                <td class="px-6 py-4 text-sm text-slate-600">{{ $event->venue ?? '—' }}</td>
                <td class="px-6 py-4">
                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full
                        {{ $event->status === 'draft' ? 'bg-amber-100 text-amber-800' : '' }}
                        {{ $event->status === 'scheduled' ? 'bg-emerald-100 text-emerald-800' : '' }}
                        {{ $event->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}
                        {{ $event->status === 'completed' ? 'bg-slate-100 text-slate-800' : '' }}
                    ">
                        {{ ucfirst($event->status) }}
                    </span>
                </td>
                <td class="px-6 py-4 text-right text-sm">
                    <a href="{{ route('events.show', $event) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                    <a href="{{ route('events.edit', $event) }}" class="ml-4 text-slate-600 hover:text-slate-900">Edit</a>
                    <form method="POST" action="{{ route('events.destroy', $event) }}" class="inline ml-4" onsubmit="return confirm('Delete this event?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-6 py-12 text-center text-slate-500">No events yet. <a href="{{ route('events.create') }}" class="text-indigo-600 hover:text-indigo-900">Create your first event</a></td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($events->hasPages())
    <div class="px-6 py-4 border-t border-slate-200">
        {{ $events->links() }}
    </div>
    @endif
</div>
@endsection

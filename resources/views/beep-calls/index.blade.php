@extends('layouts.dashboard')

@section('title', 'Beep Calls - ' . config('app.name'))

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
    <div>
        <h1 class="admin-page-title">Beep calls</h1>
        <p class="admin-page-subtitle">Schedule voice reminders to attendees (Premium)</p>
    </div>
    <a href="{{ route('beep-calls.create') }}" class="admin-btn-primary">
        Schedule call
    </a>
</div>

<form method="GET" action="{{ route('beep-calls.index') }}" class="mb-6">
    <label for="event_id" class="admin-label mb-2">Filter by event</label>
    <select name="event_id" id="event_id" onchange="this.form.submit()" class="admin-input">
        <option value="">All events</option>
        @foreach($events as $e)
        <option value="{{ $e->id }}" {{ request('event_id') == $e->id ? 'selected' : '' }}>
            {{ $e->name }} ({{ $e->date->format('M j, Y') }})
        </option>
        @endforeach
    </select>
</form>

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Event</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Attendee</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Scheduled</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-ink/50">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($calls as $call)
            <tr>
                <td class="px-6 py-4 font-medium text-ink">{{ $call->event->name }}</td>
                <td class="px-6 py-4 text-sm text-ink/65">{{ $call->attendee?->name ?? '—' }} ({{ $call->phone ?: '—' }})</td>
                <td class="px-6 py-4 text-sm text-ink/65">{{ $call->call_schedule->format('M j, Y H:i') }}</td>
                <td class="px-6 py-4">
                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full
                        {{ $call->call_status === 'pending' ? 'bg-amber-100 text-amber-800' : '' }}
                        {{ $call->call_status === 'queued' ? 'bg-blue-100 text-blue-800' : '' }}
                        {{ $call->call_status === 'completed' ? 'bg-emerald-100 text-emerald-800' : '' }}
                        {{ $call->call_status === 'failed' ? 'bg-red-100 text-red-800' : '' }}
                    ">{{ ucfirst($call->call_status) }}</span>
                </td>
                <td class="px-6 py-4 text-right text-sm space-x-2">
                    @if(in_array($call->call_status, ['pending', 'queued']))
                    <form method="POST" action="{{ route('beep-calls.call-now', $call) }}" class="inline">
                        @csrf
                        <button type="submit" class="admin-link">Call now</button>
                    </form>
                    <form method="POST" action="{{ route('beep-calls.destroy', $call) }}" class="inline" onsubmit="return confirm('Cancel this call?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-800">Cancel</button>
                    </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-6 py-12 text-center text-ink/50">
                    No beep calls scheduled. <a href="{{ route('beep-calls.create') }}" class="admin-link">Schedule your first call</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($calls->hasPages())
    <div class="px-6 py-4 border-t border-dawn-2/80">{{ $calls->links() }}</div>
    @endif
</div>
@endsection

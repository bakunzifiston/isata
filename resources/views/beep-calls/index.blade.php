@extends('layouts.dashboard')

@section('title', 'Beep Calls - ' . config('app.name'))

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Beep calls</h1>
        <p class="mt-1 text-slate-600">Schedule voice reminders to attendees (Premium)</p>
    </div>
    <a href="{{ route('beep-calls.create') }}" class="px-4 py-2 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-700">
        Schedule call
    </a>
</div>

<form method="GET" action="{{ route('beep-calls.index') }}" class="mb-6">
    <label for="event_id" class="block text-sm font-medium text-slate-700 mb-2">Filter by event</label>
    <select name="event_id" id="event_id" onchange="this.form.submit()" class="px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500">
        <option value="">All events</option>
        @foreach($events as $e)
        <option value="{{ $e->id }}" {{ request('event_id') == $e->id ? 'selected' : '' }}>
            {{ $e->name }} ({{ $e->date->format('M j, Y') }})
        </option>
        @endforeach
    </select>
</form>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <table class="min-w-full divide-y divide-slate-200">
        <thead class="bg-slate-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Event</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Attendee</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Scheduled</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            @forelse($calls as $call)
            <tr class="hover:bg-slate-50">
                <td class="px-6 py-4 font-medium text-slate-900">{{ $call->event->name }}</td>
                <td class="px-6 py-4 text-sm text-slate-600">{{ $call->attendee?->name ?? '—' }} ({{ $call->phone ?: '—' }})</td>
                <td class="px-6 py-4 text-sm text-slate-600">{{ $call->call_schedule->format('M j, Y H:i') }}</td>
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
                        <button type="submit" class="text-indigo-600 hover:text-indigo-900">Call now</button>
                    </form>
                    <form method="POST" action="{{ route('beep-calls.destroy', $call) }}" class="inline" onsubmit="return confirm('Cancel this call?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-900">Cancel</button>
                    </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                    No beep calls scheduled. <a href="{{ route('beep-calls.create') }}" class="text-indigo-600 hover:text-indigo-900">Schedule your first call</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($calls->hasPages())
    <div class="px-6 py-4 border-t border-slate-200">{{ $calls->links() }}</div>
    @endif
</div>
@endsection

@extends('layouts.dashboard')

@section('title', 'RSVP Dashboard - ' . config('app.name'))

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">RSVP dashboard</h1>
        <p class="mt-1 text-slate-600">Attendance and response tracking</p>
    </div>
</div>

@if($events->isEmpty())
<div class="bg-white rounded-xl border border-slate-200 p-12 text-center text-slate-500">
    No scheduled events yet. Create an event and add attendees to track RSVPs.
</div>
@else
<form method="GET" action="{{ route('rsvp.dashboard') }}" class="mb-6">
    <label for="event_id" class="block text-sm font-medium text-slate-700 mb-2">Select event</label>
    <select name="event_id" id="event_id" onchange="this.form.submit()" class="px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500">
        @foreach($events as $e)
        <option value="{{ $e->id }}" {{ $selectedEvent && $selectedEvent->id === $e->id ? 'selected' : '' }}>
            {{ $e->name }} ({{ $e->date->format('M j, Y') }})
        </option>
        @endforeach
    </select>
</form>

@if($selectedEvent)
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">Attendance overview</h2>
        <div class="space-y-4">
            <div class="flex justify-between items-center">
                <span class="text-slate-600">Attended</span>
                <span class="font-semibold text-emerald-600">{{ $stats['attended'] }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-slate-600">Confirmed</span>
                <span class="font-semibold text-emerald-500">{{ $stats['confirmed'] }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-slate-600">Pending</span>
                <span class="font-semibold text-amber-600">{{ $stats['pending'] }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-slate-600">No-show</span>
                <span class="font-semibold text-red-600">{{ $stats['no_show'] }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-slate-600">Declined</span>
                <span class="font-semibold text-slate-600">{{ $stats['declined'] }}</span>
            </div>
            <div class="flex justify-between items-center pt-4 border-t border-slate-200">
                <span class="font-medium text-slate-900">Total attendees</span>
                <span class="font-bold text-slate-900">{{ $stats['total'] }}</span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">Attendance pie chart</h2>
        <div class="flex items-center justify-center min-h-[280px]">
            <canvas id="attendance-chart" width="280" height="280"></canvas>
        </div>
        <div class="mt-4 flex flex-wrap gap-4 justify-center">
            <span class="flex items-center gap-2 text-sm"><span class="w-3 h-3 rounded-full bg-emerald-500"></span> Attended</span>
            <span class="flex items-center gap-2 text-sm"><span class="w-3 h-3 rounded-full bg-amber-500"></span> Pending</span>
            <span class="flex items-center gap-2 text-sm"><span class="w-3 h-3 rounded-full bg-red-500"></span> No-show</span>
            <span class="flex items-center gap-2 text-sm"><span class="w-3 h-3 rounded-full bg-slate-500"></span> Declined</span>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <h2 class="px-6 py-4 text-sm font-medium text-slate-700 bg-slate-50 border-b border-slate-200">Recent RSVP responses</h2>
    @php
        $recentRsvps = $selectedEvent->rsvps()->with('attendee')->latest('responded_at')->take(10)->get();
    @endphp
    @if($recentRsvps->isEmpty())
    <p class="px-6 py-8 text-slate-500 text-center">No RSVP responses yet. Share the {rsvp_link} tag in your messages.</p>
    @else
    <table class="min-w-full divide-y divide-slate-200">
        <thead class="bg-slate-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Attendee</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Response</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Channel</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Responded at</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            @foreach($recentRsvps as $rsvp)
            <tr class="hover:bg-slate-50">
                <td class="px-6 py-4 font-medium text-slate-900">{{ $rsvp->attendee->name }}</td>
                <td class="px-6 py-4">
                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full
                        {{ $rsvp->response === 'Yes' ? 'bg-emerald-100 text-emerald-800' : '' }}
                        {{ $rsvp->response === 'No' ? 'bg-slate-100 text-slate-800' : '' }}
                        {{ $rsvp->response === 'Maybe' ? 'bg-amber-100 text-amber-800' : '' }}
                    ">{{ $rsvp->response }}</span>
                </td>
                <td class="px-6 py-4 text-sm text-slate-600">{{ ucfirst($rsvp->response_channel ?? '—') }}</td>
                <td class="px-6 py-4 text-sm text-slate-600">{{ $rsvp->responded_at->format('M j, Y H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

<a href="{{ route('events.attendees.index', $selectedEvent) }}" class="inline-block mt-6 text-sm text-slate-600 hover:text-slate-900">Manage attendees →</a>
@endif
@endif
@endsection

@push('scripts')
@if($selectedEvent && $chartData)
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('attendance-chart');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: @json($chartData['labels']),
            datasets: [{
                data: @json($chartData['values']),
                backgroundColor: @json($chartData['colors']),
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false }
            }
        }
    });
});
</script>
@endif
@endpush

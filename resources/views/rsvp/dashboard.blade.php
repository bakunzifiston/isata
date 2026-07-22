@extends('layouts.dashboard')

@section('title', 'RSVP Dashboard - ' . config('app.name'))

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
    <div>
        <h1 class="admin-page-title">RSVP dashboard</h1>
        <p class="admin-page-subtitle">Attendance and response tracking</p>
    </div>
</div>

@if($events->isEmpty())
<div class="admin-card p-12 text-center text-ink/50">
    No scheduled events yet. Create an event and add attendees to track RSVPs.
</div>
@else
<form method="GET" action="{{ route('rsvp.dashboard') }}" class="mb-6">
    <label for="event_id" class="admin-label mb-2">Select event</label>
    <select name="event_id" id="event_id" onchange="this.form.submit()" class="admin-input">
        @foreach($events as $e)
        <option value="{{ $e->id }}" {{ $selectedEvent && $selectedEvent->id === $e->id ? 'selected' : '' }}>
            {{ $e->name }} ({{ $e->date->format('M j, Y') }})
        </option>
        @endforeach
    </select>
</form>

@if($selectedEvent)
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="admin-card p-6">
        <h2 class="text-lg font-semibold text-ink mb-4">Attendance overview</h2>
        <div class="space-y-4">
            <div class="flex justify-between items-center">
                <span class="text-ink/65">Attended</span>
                <span class="font-semibold text-emerald-600">{{ $stats['attended'] }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-ink/65">Confirmed</span>
                <span class="font-semibold text-emerald-500">{{ $stats['confirmed'] }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-ink/65">Pending</span>
                <span class="font-semibold text-amber-600">{{ $stats['pending'] }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-ink/65">No-show</span>
                <span class="font-semibold text-red-600">{{ $stats['no_show'] }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-ink/65">Declined</span>
                <span class="font-semibold text-ink/65">{{ $stats['declined'] }}</span>
            </div>
            <div class="flex justify-between items-center pt-4 border-t border-dawn-2/80">
                <span class="font-medium text-ink">Total attendees</span>
                <span class="font-bold text-ink">{{ $stats['total'] }}</span>
            </div>
        </div>
    </div>

    <div class="admin-card p-6">
        <h2 class="text-lg font-semibold text-ink mb-4">Attendance pie chart</h2>
        <div class="flex items-center justify-center min-h-[280px]">
            <canvas id="attendance-chart" width="280" height="280"></canvas>
        </div>
        <div class="mt-4 flex flex-wrap gap-4 justify-center">
            <span class="flex items-center gap-2 text-sm"><span class="w-3 h-3 rounded-full bg-emerald-500"></span> Attended</span>
            <span class="flex items-center gap-2 text-sm"><span class="w-3 h-3 rounded-full bg-amber-500"></span> Pending</span>
            <span class="flex items-center gap-2 text-sm"><span class="w-3 h-3 rounded-full bg-red-500"></span> No-show</span>
            <span class="flex items-center gap-2 text-sm"><span class="w-3 h-3 rounded-full bg-dawn-2/300"></span> Declined</span>
        </div>
    </div>
</div>

<div class="admin-table-wrap">
    <h2 class="px-6 py-4 text-sm font-medium text-ink/75 bg-dawn-2/30 border-b border-dawn-2/80">Recent RSVP responses</h2>
    @php
        $recentRsvps = $selectedEvent->rsvps()->with('attendee')->latest('responded_at')->take(10)->get();
    @endphp
    @if($recentRsvps->isEmpty())
    <p class="px-6 py-8 text-ink/50 text-center">No RSVP responses yet. Share the {rsvp_link} tag in your messages.</p>
    @else
    <table class="admin-table">
        <thead>
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Attendee</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Response</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Channel</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Responded at</th>
            </tr>
        </thead>
        <tbody>
            @foreach($recentRsvps as $rsvp)
            <tr>
                <td class="px-6 py-4 font-medium text-ink">{{ $rsvp->attendee->name }}</td>
                <td class="px-6 py-4">
                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full
                        {{ $rsvp->response === 'Yes' ? 'bg-emerald-100 text-emerald-800' : '' }}
                        {{ $rsvp->response === 'No' ? 'bg-dawn-2 text-ink/70' : '' }}
                        {{ $rsvp->response === 'Maybe' ? 'bg-amber-100 text-amber-800' : '' }}
                    ">{{ $rsvp->response }}</span>
                </td>
                <td class="px-6 py-4 text-sm text-ink/65">{{ ucfirst($rsvp->response_channel ?? '—') }}</td>
                <td class="px-6 py-4 text-sm text-ink/65">{{ $rsvp->responded_at->format('M j, Y H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

<a href="{{ route('events.attendees.index', $selectedEvent) }}" class="inline-block mt-6 text-sm text-ink/65 hover:text-ink">Manage attendees →</a>
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

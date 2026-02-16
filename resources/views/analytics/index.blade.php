@extends('layouts.dashboard')

@section('title', 'Analytics - ' . config('app.name'))

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Analytics</h1>
        <p class="mt-1 text-slate-600">Metrics and performance insights</p>
    </div>
</div>

<form method="GET" action="{{ route('analytics.index') }}" class="mb-6">
    <label for="event_id" class="block text-sm font-medium text-slate-700 mb-2">Filter by event</label>
    <select name="event_id" id="event_id" onchange="this.form.submit()" class="px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
        <option value="">All events</option>
        @foreach($events as $e)
        <option value="{{ $e->id }}" {{ request('event_id') == $e->id ? 'selected' : '' }}>
            {{ $e->name }} ({{ $e->date->format('M j, Y') }})
        </option>
        @endforeach
    </select>
</form>

{{-- KPI cards --}}
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm hover:shadow-md transition-shadow">
        <p class="text-xs font-medium text-slate-500 uppercase">Messages sent</p>
        <p class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($kpis['messages_sent']) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm hover:shadow-md transition-shadow">
        <p class="text-xs font-medium text-slate-500 uppercase">Delivery rate</p>
        <p class="mt-1 text-2xl font-bold text-emerald-600">{{ $kpis['delivery_rate'] }}%</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm hover:shadow-md transition-shadow">
        <p class="text-xs font-medium text-slate-500 uppercase">Open rate</p>
        <p class="mt-1 text-2xl font-bold text-blue-600">{{ $kpis['open_rate'] }}%</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm hover:shadow-md transition-shadow">
        <p class="text-xs font-medium text-slate-500 uppercase">RSVP rate</p>
        <p class="mt-1 text-2xl font-bold text-indigo-600">{{ $kpis['rsvp_rate'] }}%</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm hover:shadow-md transition-shadow">
        <p class="text-xs font-medium text-slate-500 uppercase">Attendance %</p>
        <p class="mt-1 text-2xl font-bold text-amber-600">{{ $kpis['attendance_rate'] }}%</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm hover:shadow-md transition-shadow">
        <p class="text-xs font-medium text-slate-500 uppercase">Social engagement</p>
        <p class="mt-1 text-2xl font-bold text-pink-600">{{ $kpis['social_engagement'] }}%</p>
    </div>
</div>

{{-- Charts --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">Messages by channel (bar)</h2>
        <div class="h-64">
            <canvas id="bar-chart"></canvas>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">Messages sent (last 7 days)</h2>
        <div class="h-64">
            <canvas id="line-chart"></canvas>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">Engagement (pie)</h2>
        <div class="h-64 flex items-center justify-center">
            <canvas id="pie-chart"></canvas>
        </div>
    </div>
</div>

{{-- Event performance report --}}
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <h2 class="px-6 py-4 text-lg font-semibold text-slate-900 bg-slate-50 border-b border-slate-200">Event performance</h2>
    <table class="min-w-full divide-y divide-slate-200">
        <thead class="bg-slate-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Event</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Attendees</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">RSVP rate</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Attendance %</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Messages</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase">Report</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            @forelse($eventPerformance as $perf)
            <tr class="hover:bg-slate-50">
                <td class="px-6 py-4 font-medium text-slate-900">{{ $perf['event']->name }}</td>
                <td class="px-6 py-4 text-sm text-slate-600">{{ $perf['attendees'] }}</td>
                <td class="px-6 py-4 text-sm text-slate-600">{{ $perf['rsvp_rate'] }}%</td>
                <td class="px-6 py-4 text-sm text-slate-600">{{ $perf['attendance_rate'] }}%</td>
                <td class="px-6 py-4 text-sm text-slate-600">{{ $perf['messages'] }}</td>
                <td class="px-6 py-4 text-right">
                    <a href="{{ route('analytics.event-report', $perf['event']) }}" class="text-indigo-600 hover:text-indigo-900 text-sm">View report</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-12 text-center text-slate-500">No events yet</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

@push('scripts')
@php
    $barLabels = $chartData['bar']['labels'] ?? [];
    $barValues = $chartData['bar']['values'] ?? [];
    $barColors = $chartData['bar']['colors'] ?? ['#6366f1', '#10b981', '#f59e0b', '#ec4899'];
    $lineLabels = $chartData['line']['labels'] ?? [];
    $lineValues = $chartData['line']['values'] ?? [];
    $pieLabels = $chartData['pie']['labels'] ?? [];
    $pieValues = $chartData['pie']['values'] ?? [];
    $pieColors = $chartData['pie']['colors'] ?? [];
@endphp
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const barLabels = @json($barLabels);
    const barValues = @json($barValues);
    const barCtx = document.getElementById('bar-chart');
    if (barCtx) {
        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: barLabels,
                datasets: [{
                    label: 'Messages',
                    data: barValues,
                    backgroundColor: @json($barColors),
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }

    const lineCtx = document.getElementById('line-chart');
    if (lineCtx) {
        new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: @json($lineLabels),
                datasets: [{
                    label: 'Messages sent',
                    data: @json($lineValues),
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }

    const pieCtx = document.getElementById('pie-chart');
    if (pieCtx) {
        new Chart(pieCtx, {
            type: 'doughnut',
            data: {
                labels: @json($pieLabels),
                datasets: [{
                    data: @json($pieValues),
                    backgroundColor: @json($pieColors),
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }
});
</script>
@endpush

@extends('layouts.super-admin')

@section('title', 'Platform Dashboard')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-900">Platform Dashboard</h1>
    <p class="mt-1 text-slate-600">Monitor everything across {{ config('app.name') }}</p>
</div>

{{-- Global KPI Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 xl:grid-cols-7 gap-4 mb-8">
    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
        <p class="text-xs font-medium text-slate-500 uppercase">Organizations</p>
        <p class="mt-1 text-2xl font-bold text-slate-900">{{ $organizations }}</p>
        <p class="text-xs text-slate-400 mt-1">{{ $activeOrgs }} active</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
        <p class="text-xs font-medium text-slate-500 uppercase">Active Subs</p>
        <p class="mt-1 text-2xl font-bold text-slate-900">{{ $activeSubscriptions }}</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
        <p class="text-xs font-medium text-slate-500 uppercase">Total Events</p>
        <p class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($totalEvents) }}</p>
        <p class="text-xs {{ $eventsGrowth >= 0 ? 'text-emerald-600' : 'text-red-600' }} mt-1">{{ $eventsGrowth >= 0 ? '+' : '' }}{{ $eventsGrowth }}% vs last month</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
        <p class="text-xs font-medium text-slate-500 uppercase">Attendees</p>
        <p class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($totalAttendees) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
        <p class="text-xs font-medium text-slate-500 uppercase">Messages Sent</p>
        <p class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($messagesSent) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
        <p class="text-xs font-medium text-slate-500 uppercase">MRR</p>
        <p class="mt-1 text-2xl font-bold text-emerald-600">${{ number_format($mrr, 2) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
        <p class="text-xs font-medium text-slate-500 uppercase">Org Growth</p>
        <p class="mt-1 text-2xl font-bold text-slate-900">{{ $orgsGrowth >= 0 ? '+' : '' }}{{ $orgsGrowth }}%</p>
        <p class="text-xs text-slate-400 mt-1">vs last month</p>
    </div>
</div>

{{-- Charts Row --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">Revenue (MRR)</h2>
        <div class="h-64">
            <canvas id="revenue-chart"></canvas>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">Plan Distribution</h2>
        <div class="h-64 flex items-center justify-center">
            <canvas id="plan-chart"></canvas>
        </div>
    </div>
</div>

{{-- Communication Analytics & System Health --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">Communication Volume</h2>
        <div class="space-y-4">
            <div class="flex justify-between items-center">
                <span class="text-sm text-slate-600">Email</span>
                <span class="font-semibold text-slate-900">{{ number_format($emailVolume) }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm text-slate-600">SMS</span>
                <span class="font-semibold text-slate-900">{{ number_format($smsVolume) }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm text-slate-600">Beep Calls</span>
                <span class="font-semibold text-slate-900">{{ number_format($beepVolume) }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm text-slate-600">Social Media</span>
                <span class="font-semibold text-slate-900">{{ number_format($socialVolume) }}</span>
            </div>
            <div class="pt-4 border-t border-slate-100">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-slate-700">Delivery Rate</span>
                    <span class="font-semibold text-emerald-600">{{ $deliveryRate }}%</span>
                </div>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">System Health</h2>
        <div class="space-y-4">
            <div class="flex justify-between items-center p-3 rounded-lg {{ $jobsCount > 0 ? 'bg-amber-50' : 'bg-slate-50' }}">
                <span class="text-sm text-slate-600">Queue (pending jobs)</span>
                <span class="font-semibold {{ $jobsCount > 0 ? 'text-amber-700' : 'text-slate-900' }}">{{ $jobsCount }}</span>
            </div>
            <div class="flex justify-between items-center p-3 rounded-lg {{ $failedCount > 0 ? 'bg-red-50' : 'bg-slate-50' }}">
                <span class="text-sm text-slate-600">Failed jobs</span>
                <span class="font-semibold {{ $failedCount > 0 ? 'text-red-700' : 'text-slate-900' }}">{{ $failedCount }}</span>
            </div>
            <a href="{{ route('super-admin.system-health') }}" class="block text-center py-2 text-sm font-medium text-amber-600 hover:text-amber-800">View details →</a>
        </div>
    </div>
</div>

{{-- Top Events & Organizations --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center">
            <h2 class="text-lg font-semibold text-slate-900">Top Events by Attendees</h2>
            <a href="{{ route('super-admin.events') }}" class="text-sm font-medium text-amber-600 hover:text-amber-800">View all</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Event</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Org</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Attendees</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">RSVP %</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Attendance %</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($topEvents as $row)
                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-4 font-medium text-slate-900">{{ Str::limit($row['event']->name, 25) }}</td>
                        <td class="px-6 py-4 text-sm text-slate-600">{{ Str::limit($row['event']->organization?->name ?? '—', 15) }}</td>
                        <td class="px-6 py-4 text-sm text-slate-600">{{ $row['attendees'] }}</td>
                        <td class="px-6 py-4 text-sm text-slate-600">{{ $row['rsvp_rate'] }}%</td>
                        <td class="px-6 py-4 text-sm text-slate-600">{{ $row['attendance_rate'] }}%</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-6 py-8 text-center text-slate-500">No events yet</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center">
            <h2 class="text-lg font-semibold text-slate-900">Organizations</h2>
            <a href="{{ route('super-admin.organizations') }}" class="text-sm font-medium text-amber-600 hover:text-amber-800">View all</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Organization</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Plan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Events</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Users</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($organizationsList as $org)
                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-4 font-medium text-slate-900">{{ Str::limit($org->name, 20) }}</td>
                        <td class="px-6 py-4 text-sm text-slate-600">{{ $org->subscriptionPlan?->name ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm text-slate-600">{{ $org->events_count }}</td>
                        <td class="px-6 py-4 text-sm text-slate-600">{{ $org->users_count }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-0.5 rounded text-xs font-medium {{ $org->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                {{ $org->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-6 py-8 text-center text-slate-500">No organizations</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($organizationsList->hasPages())
        <div class="px-6 py-3 border-t border-slate-200">{{ $organizationsList->links() }}</div>
        @endif
    </div>
</div>

{{-- Activity Feed --}}
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center">
        <h2 class="text-lg font-semibold text-slate-900">Recent Activity</h2>
        <a href="{{ route('super-admin.activity') }}" class="text-sm font-medium text-amber-600 hover:text-amber-800">View all</a>
    </div>
    <ul class="divide-y divide-slate-200">
        @forelse($recentActivity as $log)
        <li class="px-6 py-4 hover:bg-slate-50">
            <div class="flex justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-slate-900">{{ $log->action }}</p>
                    <p class="text-sm text-slate-500">{{ $log->description }}</p>
                    <p class="text-xs text-slate-400 mt-1">{{ $log->user?->name ?? 'System' }} · {{ $log->created_at->diffForHumans() }}</p>
                </div>
            </div>
        </li>
        @empty
        <li class="px-6 py-12 text-center text-slate-500">No activity yet</li>
        @endforelse
    </ul>
</div>
@endsection

@push('scripts')
@php
    $revenueLabels = collect($revenueChartData)->pluck('month')->toArray();
    $revenueValues = collect($revenueChartData)->pluck('revenue')->toArray();
    $planLabels = collect($planDistribution)->pluck('name')->toArray();
    $planValues = collect($planDistribution)->pluck('count')->toArray();
    $planColors = ['#6366f1', '#10b981', '#f59e0b', '#ec4899'];
@endphp
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const revenueCtx = document.getElementById('revenue-chart');
    if (revenueCtx) {
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: @json($revenueLabels),
                datasets: [{
                    label: 'MRR',
                    data: @json($revenueValues),
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }
    const planCtx = document.getElementById('plan-chart');
    if (planCtx) {
        new Chart(planCtx, {
            type: 'doughnut',
            data: {
                labels: @json($planLabels),
                datasets: [{
                    data: @json($planValues),
                    backgroundColor: @json($planColors),
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

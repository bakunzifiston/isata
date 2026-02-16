@extends('layouts.dashboard')

@section('title', 'Dashboard - ' . config('app.name'))

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-900">
        @if ($isAdmin)
            Admin Dashboard
        @else
            Welcome back, {{ $user->name }}
        @endif
    </h1>
    <p class="mt-1 text-slate-600">
        @if ($isAdmin)
            Manage all organizations and system settings
        @else
            Here's what's happening with {{ $organization?->name ?? 'your account' }}
        @endif
    </p>
</div>

{{-- KPI Widgets --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Events</p>
                <p class="mt-1 text-3xl font-bold text-slate-900">{{ $eventCount ?? 0 }}</p>
                <p class="mt-1 text-xs text-slate-400">Scheduled & completed</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
        </div>
        <a href="{{ route('events.index') }}" class="mt-4 inline-flex text-sm font-medium text-indigo-600 hover:text-indigo-800">View events →</a>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Attendees</p>
                <p class="mt-1 text-3xl font-bold text-slate-900">{{ $attendeeCount ?? 0 }}</p>
                <p class="mt-1 text-xs text-slate-400">Across all events</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
        </div>
        <a href="{{ route('events.index') }}" class="mt-4 inline-flex text-sm font-medium text-emerald-600 hover:text-emerald-800">Manage attendees →</a>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Messages sent</p>
                <p class="mt-1 text-3xl font-bold text-slate-900">{{ $messagesSent ?? 0 }}</p>
                <p class="mt-1 text-xs text-slate-400">Email, SMS, Social</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
        </div>
        <a href="{{ route('analytics.index') }}" class="mt-4 inline-flex text-sm font-medium text-amber-600 hover:text-amber-800">View analytics →</a>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Usage</p>
                <p class="mt-1 text-lg font-bold text-slate-900">This month</p>
                <p class="mt-1 text-xs text-slate-400">Events & contacts</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
        </div>
        <a href="{{ route('usage.index') }}" class="mt-4 inline-flex text-sm font-medium text-slate-600 hover:text-slate-800">View usage →</a>
    </div>
</div>

{{-- Charts & Calendar row --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">Messages sent (last 7 days)</h2>
        <div class="h-64">
            <canvas id="dashboard-chart"></canvas>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-slate-900">Upcoming events</h2>
            <a href="{{ route('events.calendar') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">Calendar →</a>
        </div>
        <ul class="space-y-3">
            @forelse($upcomingEvents ?? [] as $event)
                <li>
                    <a href="{{ route('events.show', $event) }}" class="block p-3 rounded-lg border border-slate-100 hover:border-indigo-200 hover:bg-indigo-50/30 transition">
                        <p class="font-medium text-slate-900">{{ $event->name }}</p>
                        <p class="text-sm text-slate-500">{{ $event->date?->format('M j, Y') }}{{ $event->time_formatted ? ' · ' . $event->time_formatted : '' }}</p>
                    </a>
                </li>
            @empty
                <li class="py-6 text-center text-slate-500 text-sm">No upcoming events</li>
            @endforelse
        </ul>
    </div>
</div>

{{-- Quick actions --}}
@if(!$isAdmin && auth()->user()->belongsToOrganization())
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
    <a href="{{ route('events.create') }}" class="flex items-center gap-4 p-6 bg-white rounded-xl border border-slate-200 shadow-sm hover:border-indigo-300 hover:shadow-md transition group">
        <div class="w-12 h-12 rounded-xl bg-indigo-100 group-hover:bg-indigo-200 flex items-center justify-center transition">
            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        </div>
        <div>
            <h3 class="font-semibold text-slate-900">Create event</h3>
            <p class="text-sm text-slate-500">Schedule a new event</p>
        </div>
    </a>
    <a href="{{ route('events.calendar') }}" class="flex items-center gap-4 p-6 bg-white rounded-xl border border-slate-200 shadow-sm hover:border-indigo-300 hover:shadow-md transition group">
        <div class="w-12 h-12 rounded-xl bg-emerald-100 group-hover:bg-emerald-200 flex items-center justify-center transition">
            <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </div>
        <div>
            <h3 class="font-semibold text-slate-900">View calendar</h3>
            <p class="text-sm text-slate-500">See all events</p>
        </div>
    </a>
    <a href="{{ route('analytics.index') }}" class="flex items-center gap-4 p-6 bg-white rounded-xl border border-slate-200 shadow-sm hover:border-indigo-300 hover:shadow-md transition group">
        <div class="w-12 h-12 rounded-xl bg-amber-100 group-hover:bg-amber-200 flex items-center justify-center transition">
            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        </div>
        <div>
            <h3 class="font-semibold text-slate-900">Analytics</h3>
            <p class="text-sm text-slate-500">View metrics</p>
        </div>
    </a>
    <a href="{{ route('usage.index') }}" class="flex items-center gap-4 p-6 bg-white rounded-xl border border-slate-200 shadow-sm hover:border-indigo-300 hover:shadow-md transition group">
        <div class="w-12 h-12 rounded-xl bg-slate-100 group-hover:bg-slate-200 flex items-center justify-center transition">
            <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
        </div>
        <div>
            <h3 class="font-semibold text-slate-900">Usage & plans</h3>
            <p class="text-sm text-slate-500">Subscription details</p>
        </div>
    </a>
</div>
@endif
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('dashboard-chart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($chartData['labels'] ?? []),
                datasets: [{
                    label: 'Messages sent',
                    data: @json($chartData['values'] ?? []),
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
});
</script>
@endpush

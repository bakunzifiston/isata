@extends('layouts.dashboard')

@section('title', 'Dashboard - ' . config('app.name'))

@section('content')
<div class="mb-8">
    <h1 class="admin-page-title">
        @if ($isAdmin)
            Admin dashboard
        @else
            Welcome back, {{ $user->name }}
        @endif
    </h1>
    <p class="admin-page-subtitle">
        @if ($isAdmin)
            Manage all organizations and system settings
        @else
            Here's what's happening with {{ $organization?->name ?? 'your account' }}
        @endif
    </p>
</div>

<div class="mb-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
    <x-admin.card class="admin-card-hover p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="font-mono text-xs uppercase tracking-wider text-ink/50">Events</p>
                <p class="mt-1 font-display text-3xl font-semibold text-ink">{{ $eventCount ?? 0 }}</p>
                <p class="mt-1 text-xs text-ink/45">Scheduled & completed</p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-coral/10">
                <svg class="h-6 w-6 text-coral" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
        </div>
        <a href="{{ route('events.index') }}" class="mt-4 inline-flex text-sm font-medium text-coral hover:text-coral/80">View events →</a>
    </x-admin.card>

    <x-admin.card class="admin-card-hover p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="font-mono text-xs uppercase tracking-wider text-ink/50">Attendees</p>
                <p class="mt-1 font-display text-3xl font-semibold text-ink">{{ $attendeeCount ?? 0 }}</p>
                <p class="mt-1 text-xs text-ink/45">Across all events</p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-sage/15">
                <svg class="h-6 w-6 text-sage" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
        </div>
        <a href="{{ route('contacts.index') }}" class="mt-4 inline-flex text-sm font-medium text-sage hover:text-sage/80">Manage contacts →</a>
    </x-admin.card>

    <x-admin.card class="admin-card-hover p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="font-mono text-xs uppercase tracking-wider text-ink/50">Messages sent</p>
                <p class="mt-1 font-display text-3xl font-semibold text-ink">{{ $messagesSent ?? 0 }}</p>
                <p class="mt-1 text-xs text-ink/45">Email, SMS, social</p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-signal/15">
                <svg class="h-6 w-6 text-signal" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
        </div>
        <a href="{{ route('analytics.index') }}" class="mt-4 inline-flex text-sm font-medium text-signal hover:text-signal/80">View analytics →</a>
    </x-admin.card>

    <x-admin.card class="admin-card-hover p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="font-mono text-xs uppercase tracking-wider text-ink/50">Usage</p>
                <p class="mt-1 font-display text-lg font-semibold text-ink">This month</p>
                <p class="mt-1 text-xs text-ink/45">Events & contacts</p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-dawn-2">
                <svg class="h-6 w-6 text-ink/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
        </div>
        <a href="{{ route('usage.index') }}" class="mt-4 inline-flex text-sm font-medium text-ink/70 hover:text-ink">View usage →</a>
    </x-admin.card>
</div>

<div class="mb-8 grid grid-cols-1 gap-6 lg:grid-cols-3">
    <x-admin.card class="p-6 lg:col-span-2">
        <h2 class="font-display text-lg font-semibold text-ink">Messages sent (last 7 days)</h2>
        <div class="mt-4 h-64">
            <canvas id="dashboard-chart"></canvas>
        </div>
    </x-admin.card>

    <x-admin.card class="p-6">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="font-display text-lg font-semibold text-ink">Upcoming events</h2>
            <a href="{{ route('events.calendar') }}" class="text-sm font-medium text-coral hover:text-coral/80">Calendar →</a>
        </div>
        <ul class="space-y-3">
            @forelse($upcomingEvents ?? [] as $event)
                <li>
                    <a href="{{ route('events.show', $event) }}" class="block rounded-lg border border-dawn-2/80 p-3 transition hover:border-coral/25 hover:bg-coral/5">
                        <p class="font-medium text-ink">{{ $event->name }}</p>
                        <p class="text-sm text-ink/55">{{ $event->date?->format('M j, Y') }}{{ $event->time_formatted ? ' · '.$event->time_formatted : '' }}</p>
                    </a>
                </li>
            @empty
                <li class="py-6 text-center text-sm text-ink/50">No upcoming events</li>
            @endforelse
        </ul>
    </x-admin.card>
</div>

@if(!$isAdmin && auth()->user()->belongsToOrganization())
<div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
    @foreach([
        ['route' => 'events.create', 'title' => 'Create event', 'desc' => 'Schedule a new event', 'icon' => 'M12 4v16m8-8H4', 'bg' => 'bg-coral/10', 'text' => 'text-coral'],
        ['route' => 'events.calendar', 'title' => 'View calendar', 'desc' => 'See all events', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'bg' => 'bg-sage/15', 'text' => 'text-sage'],
        ['route' => 'analytics.index', 'title' => 'Analytics', 'desc' => 'View metrics', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'bg' => 'bg-signal/15', 'text' => 'text-signal'],
        ['route' => 'usage.index', 'title' => 'Usage & plans', 'desc' => 'Subscription details', 'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z', 'bg' => 'bg-dawn-2', 'text' => 'text-ink/65'],
    ] as $action)
    <a href="{{ route($action['route']) }}" class="admin-card-hover flex items-center gap-4 p-6 group">
        <div class="flex h-12 w-12 items-center justify-center rounded-xl {{ $action['bg'] }} transition group-hover:scale-105">
            <svg class="h-6 w-6 {{ $action['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $action['icon'] }}"/></svg>
        </div>
        <div>
            <h3 class="font-semibold text-ink">{{ $action['title'] }}</h3>
            <p class="text-sm text-ink/55">{{ $action['desc'] }}</p>
        </div>
    </a>
    @endforeach
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
                    borderColor: '#E8604C',
                    backgroundColor: 'rgba(232, 96, 76, 0.12)',
                    fill: true,
                    tension: 0.35
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(33, 28, 28, 0.06)' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }
});
</script>
@endpush

@extends('layouts.dashboard')

@section('title', 'Usage - ' . config('app.name'))

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="admin-page-title">Usage & subscription</h1>
        <p class="admin-page-subtitle">Monitor your organization's usage for {{ \Carbon\Carbon::parse($currentPeriod . '-01')->format('F Y') }}</p>
    </div>
    <a href="{{ route('subscription.plans') }}" class="inline-flex items-center gap-2 admin-btn-primary">
        View plans
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    {{-- Events --}}
    <div class="admin-card p-6 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-ink/50">Events this month</p>
                <p class="admin-stat-value">{{ $usage->events_count }}</p>
                <p class="text-xs text-ink/50 mt-1">
                    @if($eventsLimit)
                        Limit: {{ $eventsLimit }}
                    @else
                        Unlimited
                    @endif
                </p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-coral/10 flex items-center justify-center">
                <svg class="w-6 h-6 text-coral" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
        </div>
        @if($eventsLimit)
        <div class="mt-4 h-2 bg-dawn-2 rounded-full overflow-hidden">
            <div class="h-full bg-coral rounded-full" style="width: {{ min(100, ($usage->events_count / $eventsLimit) * 100) }}%"></div>
        </div>
        @endif
    </div>

    {{-- Contacts --}}
    <div class="admin-card p-6 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-ink/50">Contacts</p>
                <p class="admin-stat-value">{{ $usage->contacts_count }}</p>
                <p class="text-xs text-ink/50 mt-1">
                    @if($contactsLimit)
                        Limit: {{ number_format($contactsLimit) }}
                    @else
                        Unlimited
                    @endif
                </p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-emerald-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
        </div>
        @if($contactsLimit)
        <div class="mt-4 h-2 bg-dawn-2 rounded-full overflow-hidden">
            <div class="h-full bg-emerald-600 rounded-full" style="width: {{ min(100, ($usage->contacts_count / $contactsLimit) * 100) }}%"></div>
        </div>
        @endif
    </div>

    {{-- Beep calls --}}
    <div class="admin-card p-6 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-ink/50">Beep calls</p>
                <p class="admin-stat-value">{{ $usage->beep_calls_count }}</p>
                <p class="text-xs text-ink/50 mt-1">
                    {{ $hasBeepCalls ? 'Included in plan' : 'Not in plan' }}
                </p>
            </div>
            <div class="w-12 h-12 rounded-lg {{ $hasBeepCalls ? 'bg-amber-100' : 'bg-dawn-2' }} flex items-center justify-center">
                <svg class="w-6 h-6 {{ $hasBeepCalls ? 'text-amber-600' : 'text-ink/45' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
            </div>
        </div>
    </div>
</div>

<div class="admin-card p-6">
    <h2 class="text-lg font-semibold text-ink mb-4">Current plan: {{ $plan?->name ?? 'None' }}</h2>
    <p class="text-ink/65 text-sm">
        @if($plan)
            You're on the {{ $plan->name }} plan. Events and contacts are tracked monthly.
            @if(!$hasBeepCalls)
                Upgrade to Premium for beep call capabilities.
            @endif
        @else
            No subscription plan assigned. Contact support to get started.
        @endif
    </p>
</div>
@endsection

@extends('layouts.dashboard')

@section('title', 'Upgrade Subscription - ' . config('app.name'))

@section('content')
<div class="mb-8">
    <h1 class="admin-page-title">Upgrade subscription</h1>
    <p class="admin-page-subtitle">Choose a plan that fits your needs</p>
</div>

<div class="mb-6 p-4 rounded-lg bg-dawn-2 text-ink/75">
    <p class="text-sm">Current plan: <strong>{{ $currentPlan?->name ?? 'None' }}</strong></p>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    @foreach($plans as $plan)
    <div class="overflow-hidden rounded-xl border-2 bg-white {{ $currentPlan?->id === $plan->id ? 'border-dawn-2' : ($preselectedSlug === $plan->slug ? 'border-coral ring-2 ring-coral/20' : 'border-dawn-2/80 hover:border-coral/30') }}">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-ink">{{ $plan->name }}</h3>
            <div class="mt-2">
                <span class="text-3xl font-bold text-ink">${{ number_format($plan->price, 0) }}</span>
                <span class="text-ink/50">/month</span>
            </div>
            <ul class="mt-4 space-y-2 text-sm text-ink/65">
                <li>{{ $plan->formatLimit('events_per_month') }}</li>
                <li>{{ $plan->formatLimit('contacts') }} contacts</li>
                <li>Beep calls {{ $plan->hasBeepCalls() ? 'included' : '—' }}</li>
            </ul>
            @if($currentPlan?->id === $plan->id)
            <p class="mt-6 text-center text-sm text-ink/50 py-2">Current plan</p>
            @else
            <form method="POST" action="{{ route('subscription.upgrade.store') }}" class="mt-6">
                @csrf
                <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                <button type="submit" class="admin-btn-primary w-full py-2">
                    {{ $plan->price > ($currentPlan?->price ?? -1) ? 'Upgrade to ' . $plan->name : 'Switch to ' . $plan->name }}
                </button>
            </form>
            @endif
        </div>
    </div>
    @endforeach
</div>

<a href="{{ route('subscription.plans') }}" class="inline-block mt-6 text-sm text-ink/65 hover:text-ink">← Back to plan comparison</a>
@endsection

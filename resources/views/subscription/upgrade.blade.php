@extends('layouts.dashboard')

@section('title', 'Upgrade Subscription - ' . config('app.name'))

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-900">Upgrade subscription</h1>
    <p class="mt-1 text-slate-600">Choose a plan that fits your needs</p>
</div>

<div class="mb-6 p-4 rounded-lg bg-slate-100 text-slate-700">
    <p class="text-sm">Current plan: <strong>{{ $currentPlan?->name ?? 'None' }}</strong></p>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    @foreach($plans as $plan)
    <div class="bg-white rounded-xl border-2 {{ $currentPlan?->id === $plan->id ? 'border-slate-300' : ($preselectedSlug === $plan->slug ? 'border-indigo-400 ring-2 ring-indigo-200' : 'border-slate-200 hover:border-indigo-300') }} overflow-hidden">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-slate-900">{{ $plan->name }}</h3>
            <div class="mt-2">
                <span class="text-3xl font-bold text-slate-900">${{ number_format($plan->price, 0) }}</span>
                <span class="text-slate-500">/month</span>
            </div>
            <ul class="mt-4 space-y-2 text-sm text-slate-600">
                <li>{{ $plan->formatLimit('events_per_month') }}</li>
                <li>{{ $plan->formatLimit('contacts') }} contacts</li>
                <li>Beep calls {{ $plan->hasBeepCalls() ? 'included' : '—' }}</li>
            </ul>
            @if($currentPlan?->id === $plan->id)
            <p class="mt-6 text-center text-sm text-slate-500 py-2">Current plan</p>
            @else
            <form method="POST" action="{{ route('subscription.upgrade.store') }}" class="mt-6">
                @csrf
                <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                <button type="submit" class="w-full py-2 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-700">
                    {{ $plan->price > ($currentPlan?->price ?? -1) ? 'Upgrade to ' . $plan->name : 'Switch to ' . $plan->name }}
                </button>
            </form>
            @endif
        </div>
    </div>
    @endforeach
</div>

<a href="{{ route('subscription.plans') }}" class="inline-block mt-6 text-sm text-slate-600 hover:text-slate-900">← Back to plan comparison</a>
@endsection

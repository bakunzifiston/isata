@extends('layouts.dashboard')

@section('title', 'Plans - ' . config('app.name'))

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-900">Plan comparison</h1>
    <p class="mt-1 text-slate-600">Compare plans and choose the right one for your organization</p>
</div>

@if($currentPlan)
<div class="mb-6 p-4 rounded-lg bg-indigo-50 text-indigo-800 text-sm">
    Your current plan: <strong>{{ $currentPlan->name }}</strong>
    <a href="{{ route('subscription.upgrade') }}" class="ml-2 font-medium underline">Upgrade</a>
</div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    @foreach($plans as $plan)
    <div class="bg-white rounded-xl border-2 {{ $currentPlan?->id === $plan->id ? 'border-indigo-500 shadow-lg' : 'border-slate-200' }} overflow-hidden">
        @if($currentPlan?->id === $plan->id)
        <div class="bg-indigo-600 text-white text-center py-2 text-sm font-medium">Current plan</div>
        @elseif($plan->slug === 'premium')
        <div class="bg-amber-500 text-white text-center py-2 text-sm font-medium">Most popular</div>
        @endif
        <div class="p-6">
            <h3 class="text-lg font-semibold text-slate-900">{{ $plan->name }}</h3>
            <div class="mt-2">
                <span class="text-3xl font-bold text-slate-900">${{ number_format($plan->price, 0) }}</span>
                <span class="text-slate-500">/month</span>
            </div>
            <ul class="mt-6 space-y-3">
                <li class="flex items-center gap-2 text-sm text-slate-600">
                    <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    {{ $plan->formatLimit('events_per_month') }}
                </li>
                <li class="flex items-center gap-2 text-sm text-slate-600">
                    <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    {{ $plan->formatLimit('contacts') }} contacts
                </li>
                <li class="flex items-center gap-2 text-sm text-slate-600">
                    <svg class="w-5 h-5 {{ $plan->hasBeepCalls() ? 'text-emerald-500' : 'text-slate-300' }} shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    Beep calls {{ $plan->hasBeepCalls() ? 'included' : '—' }}
                </li>
            </ul>
            @if(auth()->user()->isOrganizationAdmin() && $currentPlan?->id !== $plan->id && $plan->price > 0)
            <a href="{{ route('subscription.upgrade') }}?plan={{ $plan->slug }}" class="mt-6 block w-full py-2 text-center rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-700">
                {{ $plan->price > ($currentPlan?->price ?? 0) ? 'Upgrade' : 'Switch' }}
            </a>
            @elseif($plan->price === 0 && $currentPlan?->id === $plan->id)
            <p class="mt-6 text-center text-sm text-slate-500">Free forever</p>
            @endif
        </div>
    </div>
    @endforeach
</div>
@endsection

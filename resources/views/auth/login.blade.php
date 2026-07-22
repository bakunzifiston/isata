@extends('layouts.auth')

@section('title', 'Log in — ' . config('app.name'))

@section('content')
<div class="rounded-[22px] border border-ink/8 bg-white p-8 shadow-sm">
    <p class="font-mono text-xs uppercase tracking-widest text-ink/45">Account</p>
    <h1 class="mt-2 font-display text-2xl font-semibold tracking-tight">Welcome back</h1>
    <p class="mt-1 text-sm text-ink/65">Sign in to your organization dashboard.</p>

    @if (session('status'))
        <div class="mt-6 rounded-xl border border-sage/30 bg-sage/10 px-4 py-3 text-sm text-ink">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mt-6 rounded-xl border border-coral/30 bg-coral/10 px-4 py-3 text-sm text-ink">
            <ul class="space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-5">
        @csrf

        <x-auth-input
            label="Email"
            name="email"
            type="email"
            :value="old('email')"
            placeholder="you@example.com"
            required
            autofocus
            autocomplete="email"
        />

        <x-auth-input
            label="Password"
            name="password"
            type="password"
            placeholder="••••••••"
            required
            autocomplete="current-password"
        />

        <div class="flex items-center">
            <input
                type="checkbox"
                name="remember"
                id="remember"
                class="rounded border-ink/20 text-signal focus:ring-signal"
            >
            <label for="remember" class="ml-2 text-sm text-ink/65">Remember me</label>
        </div>

        <button
            type="submit"
            class="w-full rounded-full bg-coral px-4 py-3 text-sm font-semibold text-white transition hover:bg-coral/90 focus:outline-none focus:ring-2 focus:ring-coral focus:ring-offset-2"
        >
            Sign in
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-ink/65">
        Don't have an account?
        <a href="{{ route('register') }}" class="font-medium text-coral hover:text-coral/80">Register your organization</a>
    </p>
</div>
@endsection

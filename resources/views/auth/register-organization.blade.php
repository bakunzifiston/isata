@extends('layouts.auth')

@section('title', 'Register — ' . config('app.name'))

@section('content')
<div class="rounded-[22px] border border-ink/8 bg-white p-8 shadow-sm">
    <p class="font-mono text-xs uppercase tracking-widest text-ink/45">Get started</p>
    <h1 class="mt-2 font-display text-2xl font-semibold tracking-tight">Create your organization</h1>
    <p class="mt-1 text-sm text-ink/65">Free to start. Upgrade when your events grow.</p>

    @if ($errors->any())
        <div class="mt-6 rounded-xl border border-coral/30 bg-coral/10 px-4 py-3 text-sm text-ink">
            <ul class="space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-5">
        @csrf

        <x-auth-input
            label="Organization name"
            name="organization_name"
            :value="old('organization_name')"
            placeholder="Acme Inc."
            required
            autofocus
        />

        <div class="border-t border-ink/8 pt-5">
            <p class="mb-4 font-mono text-xs uppercase tracking-widest text-ink/45">Your admin account</p>

            <div class="space-y-4">
                <x-auth-input
                    label="Full name"
                    name="name"
                    :value="old('name')"
                    placeholder="Jane Doe"
                    required
                />

                <x-auth-input
                    label="Email"
                    name="email"
                    type="email"
                    :value="old('email')"
                    placeholder="you@example.com"
                    required
                    autocomplete="email"
                />

                <x-auth-input
                    label="Password"
                    name="password"
                    type="password"
                    placeholder="••••••••"
                    required
                    autocomplete="new-password"
                />

                <x-auth-input
                    label="Confirm password"
                    name="password_confirmation"
                    type="password"
                    placeholder="••••••••"
                    required
                    autocomplete="new-password"
                />
            </div>
        </div>

        <button
            type="submit"
            class="w-full rounded-full bg-coral px-4 py-3 text-sm font-semibold text-white transition hover:bg-coral/90 focus:outline-none focus:ring-2 focus:ring-coral focus:ring-offset-2"
        >
            Create organization
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-ink/65">
        Already have an account?
        <a href="{{ route('login') }}" class="font-medium text-coral hover:text-coral/80">Sign in</a>
    </p>
</div>
@endsection

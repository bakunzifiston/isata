@extends('layouts.app')

@section('title', 'Login - ' . config('app.name'))

@section('content')
<div class="min-h-screen flex flex-col items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
        {{-- Logo / Brand --}}
        <div class="text-center mb-10">
            <a href="{{ route('home') }}" class="inline-block">
                <span class="text-3xl font-bold tracking-tight text-slate-900">ISATA</span>
            </a>
            <p class="mt-2 text-slate-600">Event management for organizations</p>
        </div>

        {{-- Card --}}
        <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-200/80 p-8">
            <h1 class="text-2xl font-semibold text-slate-900 mb-1">Welcome back</h1>
            <p class="text-slate-600 mb-6">Sign in to your account</p>

            @if (session('status'))
                <div class="mb-4 p-3 rounded-lg bg-emerald-50 text-emerald-800 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 p-3 rounded-lg bg-red-50 text-red-800 text-sm">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">Email</label>
                    <input type="email"
                           name="email"
                           id="email"
                           value="{{ old('email') }}"
                           required
                           autofocus
                           autocomplete="email"
                           class="w-full px-4 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                           placeholder="you@example.com">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700 mb-1.5">Password</label>
                    <input type="password"
                           name="password"
                           id="password"
                           required
                           autocomplete="current-password"
                           class="w-full px-4 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                           placeholder="••••••••">
                </div>

                <div class="flex items-center">
                    <input type="checkbox"
                           name="remember"
                           id="remember"
                           class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="remember" class="ml-2 text-sm text-slate-600">Remember me</label>
                </div>

                <button type="submit"
                        class="w-full py-3 px-4 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                    Sign in
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-slate-600">
                Don't have an account?
                <a href="{{ route('register') }}" class="font-medium text-indigo-600 hover:text-indigo-500">Register your organization</a>
            </p>
        </div>

        <p class="mt-8 text-center text-sm text-slate-500">
            <a href="{{ route('home') }}" class="hover:text-slate-700">← Back to home</a>
        </p>
    </div>
</div>
@endsection

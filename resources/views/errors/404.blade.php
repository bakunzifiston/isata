@extends('layouts.app')

@section('title', 'Page not found')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4">
    <div class="max-w-lg w-full bg-white rounded-xl shadow border border-dawn-2/80 p-6">
        <h1 class="text-xl font-semibold text-ink mb-2">
            404 - Page not found
        </h1>
        <p class="text-ink/75 mb-4">
            {{ $exception?->getMessage() ?: 'The page you are looking for could not be found.' }}
        </p>
        <div class="flex justify-between items-center text-sm">
            <a href="{{ url()->previous() }}" class="text-coral hover:text-coral/80">
                ← Go back
            </a>
            <a href="{{ route('dashboard') }}" class="text-ink/65 hover:text-ink">
                Go to dashboard
            </a>
        </div>
    </div>
</div>
@endsection


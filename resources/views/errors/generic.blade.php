@extends('layouts.app')

@section('title', 'Error')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4">
    <div class="max-w-lg w-full bg-white rounded-xl shadow border border-dawn-2/80 p-6">
        <h1 class="text-xl font-semibold text-red-600 mb-2">
            Something went wrong ({{ $status ?? 500 }})
        </h1>
        <p class="text-ink/75 mb-4">
            {{ $message ?? 'An unexpected error occurred.' }}
        </p>
        <p class="text-xs text-ink/45 mb-4">
            If you keep seeing this, contact the administrator with this message.
        </p>
        <div class="flex justify-between items-center text-sm">
            <a href="{{ url()->previous() }}" class="text-coral hover:text-coral/80">
                ← Go back
            </a>
            <a href="{{ route('home') }}" class="text-ink/65 hover:text-ink">
                Home
            </a>
        </div>
    </div>
</div>
@endsection


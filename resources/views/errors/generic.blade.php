@extends('layouts.app')

@section('title', 'Error')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4">
    <div class="max-w-lg w-full bg-white rounded-xl shadow border border-slate-200 p-6">
        <h1 class="text-xl font-semibold text-red-600 mb-2">
            Something went wrong ({{ $status ?? 500 }})
        </h1>
        <p class="text-slate-700 mb-4">
            {{ $message ?? 'An unexpected error occurred.' }}
        </p>
        <p class="text-xs text-slate-400 mb-4">
            If you keep seeing this, contact the administrator with this message.
        </p>
        <div class="flex justify-between items-center text-sm">
            <a href="{{ url()->previous() }}" class="text-indigo-600 hover:text-indigo-800">
                ← Go back
            </a>
            <a href="{{ route('home') }}" class="text-slate-600 hover:text-slate-900">
                Home
            </a>
        </div>
    </div>
</div>
@endsection


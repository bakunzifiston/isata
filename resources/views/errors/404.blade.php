@extends('layouts.app')

@section('title', 'Page not found')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4">
    <div class="max-w-lg w-full bg-white rounded-xl shadow border border-slate-200 p-6">
        <h1 class="text-xl font-semibold text-slate-900 mb-2">
            404 - Page not found
        </h1>
        <p class="text-slate-700 mb-4">
            {{ $exception?->getMessage() ?: 'The page you are looking for could not be found.' }}
        </p>
        <div class="flex justify-between items-center text-sm">
            <a href="{{ url()->previous() }}" class="text-indigo-600 hover:text-indigo-800">
                ← Go back
            </a>
            <a href="{{ route('dashboard') }}" class="text-slate-600 hover:text-slate-900">
                Go to dashboard
            </a>
        </div>
    </div>
</div>
@endsection


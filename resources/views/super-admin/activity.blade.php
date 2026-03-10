@extends('layouts.super-admin')

@section('title', 'Activity Logs')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-900">Activity Logs</h1>
    <p class="mt-1 text-slate-600">User actions and security tracking</p>
</div>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <ul class="divide-y divide-slate-200">
        @forelse($logs as $log)
        <li class="px-6 py-4 hover:bg-slate-50">
            <div class="flex justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-slate-900">{{ $log->action }}</p>
                    <p class="text-sm text-slate-500">{{ $log->description }}</p>
                    <p class="text-xs text-slate-400 mt-1">
                        {{ $log->user?->name ?? 'System' }} · {{ $log->created_at->format('M j, Y H:i') }}
                        @if($log->ip_address)
                            · {{ $log->ip_address }}
                        @endif
                    </p>
                </div>
            </div>
        </li>
        @empty
        <li class="px-6 py-12 text-center text-slate-500">No activity logs</li>
        @endforelse
    </ul>
    @if($logs->hasPages())
    <div class="px-6 py-4 border-t border-slate-200">{{ $logs->links() }}</div>
    @endif
</div>
@endsection

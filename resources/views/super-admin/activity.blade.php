@extends('layouts.super-admin')

@section('title', 'Activity Logs')

@section('content')
<div class="mb-8">
    <h1 class="admin-page-title">Activity Logs</h1>
    <p class="admin-page-subtitle">User actions and security tracking</p>
</div>

<div class="admin-table-wrap">
    <ul class="divide-y divide-dawn-2/80">
        @forelse($logs as $log)
        <li class="px-6 py-4 hover:bg-dawn-2/30">
            <div class="flex justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-ink">{{ $log->action }}</p>
                    <p class="text-sm text-ink/50">{{ $log->description }}</p>
                    <p class="text-xs text-ink/45 mt-1">
                        {{ $log->user?->name ?? 'System' }} · {{ $log->created_at->format('M j, Y H:i') }}
                        @if($log->ip_address)
                            · {{ $log->ip_address }}
                        @endif
                    </p>
                </div>
            </div>
        </li>
        @empty
        <li class="px-6 py-12 text-center text-ink/50">No activity logs</li>
        @endforelse
    </ul>
    @if($logs->hasPages())
    <div class="px-6 py-4 border-t border-dawn-2/80">{{ $logs->links() }}</div>
    @endif
</div>
@endsection

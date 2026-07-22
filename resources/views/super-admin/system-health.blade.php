@extends('layouts.super-admin')

@section('title', 'System Health')

@section('content')
<div class="mb-8">
    <h1 class="admin-page-title">System Health</h1>
    <p class="admin-page-subtitle">Queue status, failed jobs, and API delivery</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <div class="admin-card p-6">
        <h2 class="text-lg font-semibold text-ink mb-2">Queue Status</h2>
        <p class="text-3xl font-bold {{ $jobsCount > 0 ? 'text-amber-600' : 'text-emerald-600' }}">{{ $jobsCount }}</p>
        <p class="text-sm text-ink/50 mt-1">Pending jobs in queue</p>
    </div>
    <div class="admin-card p-6">
        <h2 class="text-lg font-semibold text-ink mb-2">Failed Jobs</h2>
        <p class="text-3xl font-bold {{ $failedCount > 0 ? 'text-red-600' : 'text-emerald-600' }}">{{ $failedCount }}</p>
        <p class="text-sm text-ink/50 mt-1">Jobs that failed execution</p>
    </div>
</div>

<div class="admin-table-wrap">
    <h2 class="px-6 py-4 text-lg font-semibold text-ink border-b border-dawn-2/80">Failed Jobs (last 20)</h2>
    <div class="overflow-x-auto">
        <table class="admin-table">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">UUID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Connection</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Queue</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Failed At</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Exception</th>
                </tr>
            </thead>
            <tbody>
                @forelse($failedJobs as $job)
                <tr>
                    <td class="px-6 py-4 text-sm font-mono text-ink/65">{{ Str::limit($job->uuid ?? '—', 8) }}</td>
                    <td class="px-6 py-4 text-sm text-ink/65">{{ $job->connection ?? '—' }}</td>
                    <td class="px-6 py-4 text-sm text-ink/65">{{ $job->queue ?? '—' }}</td>
                    <td class="px-6 py-4 text-sm text-ink/65">{{ $job->failed_at ? \Carbon\Carbon::parse($job->failed_at)->format('M j, H:i') : '—' }}</td>
                    <td class="px-6 py-4 text-sm text-red-600 max-w-xs truncate" title="{{ $job->exception ?? '' }}">{{ Str::limit($job->exception ?? '—', 60) }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-6 py-12 text-center text-ink/50">No failed jobs</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

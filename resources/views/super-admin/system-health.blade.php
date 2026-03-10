@extends('layouts.super-admin')

@section('title', 'System Health')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-900">System Health</h1>
    <p class="mt-1 text-slate-600">Queue status, failed jobs, and API delivery</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900 mb-2">Queue Status</h2>
        <p class="text-3xl font-bold {{ $jobsCount > 0 ? 'text-amber-600' : 'text-emerald-600' }}">{{ $jobsCount }}</p>
        <p class="text-sm text-slate-500 mt-1">Pending jobs in queue</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900 mb-2">Failed Jobs</h2>
        <p class="text-3xl font-bold {{ $failedCount > 0 ? 'text-red-600' : 'text-emerald-600' }}">{{ $failedCount }}</p>
        <p class="text-sm text-slate-500 mt-1">Jobs that failed execution</p>
    </div>
</div>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <h2 class="px-6 py-4 text-lg font-semibold text-slate-900 border-b border-slate-200">Failed Jobs (last 20)</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">UUID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Connection</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Queue</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Failed At</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Exception</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($failedJobs as $job)
                <tr class="hover:bg-slate-50">
                    <td class="px-6 py-4 text-sm font-mono text-slate-600">{{ Str::limit($job->uuid ?? '—', 8) }}</td>
                    <td class="px-6 py-4 text-sm text-slate-600">{{ $job->connection ?? '—' }}</td>
                    <td class="px-6 py-4 text-sm text-slate-600">{{ $job->queue ?? '—' }}</td>
                    <td class="px-6 py-4 text-sm text-slate-600">{{ $job->failed_at ? \Carbon\Carbon::parse($job->failed_at)->format('M j, H:i') : '—' }}</td>
                    <td class="px-6 py-4 text-sm text-red-600 max-w-xs truncate" title="{{ $job->exception ?? '' }}">{{ Str::limit($job->exception ?? '—', 60) }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-6 py-12 text-center text-slate-500">No failed jobs</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

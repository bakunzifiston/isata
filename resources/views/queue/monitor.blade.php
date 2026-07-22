@extends('layouts.dashboard')

@section('title', 'Queue Status - ' . config('app.name'))

@section('content')
<div class="mb-8">
    <h1 class="admin-page-title">Queue status monitor</h1>
    <p class="admin-page-subtitle">Pending messages and queue health</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="admin-card p-6">
        <p class="text-sm font-medium text-ink/50 uppercase">Pending (scheduled)</p>
        <p class="mt-2 text-3xl font-bold text-ink">{{ $pending->count() }}</p>
        <p class="mt-1 text-sm text-ink/65">Messages waiting to be sent</p>
    </div>
    <div class="admin-card p-6">
        <p class="text-sm font-medium text-ink/50 uppercase">Due now</p>
        <p class="mt-2 text-3xl font-bold text-blue-600">{{ $due->count() }}</p>
        <p class="mt-1 text-sm text-ink/65">Ready for scheduler to dispatch</p>
    </div>
    <div class="admin-card p-6">
        <p class="text-sm font-medium text-ink/50 uppercase">Queue jobs</p>
        <p class="mt-2 text-3xl font-bold text-ink">{{ $jobsCount }}</p>
        <p class="mt-1 text-sm text-ink/65">Jobs in queue · {{ $failedCount }} failed</p>
    </div>
</div>

<div class="space-y-6">
    @if($pending->isNotEmpty())
    <div class="admin-table-wrap">
        <h2 class="px-6 py-4 text-sm font-medium text-ink/75 bg-dawn-2/30 border-b border-dawn-2/80">Scheduled messages</h2>
        <table class="admin-table">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Event</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Channel</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Scheduled at</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pending as $m)
                <tr>
                    <td class="px-6 py-4">
                        <a href="{{ route('events.show', $m->event) }}" class="admin-link">{{ $m->event->name }}</a>
                    </td>
                    <td class="px-6 py-4 text-sm text-ink/65">{{ $m->channel->name }}</td>
                    <td class="px-6 py-4 text-sm text-ink/65">{{ $m->scheduled_at?->format('M j, Y H:i') ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if($due->isNotEmpty())
    <div class="bg-white rounded-xl border border-blue-200 shadow-sm overflow-hidden">
        <h2 class="px-6 py-4 text-sm font-medium text-blue-800 bg-blue-50 border-b border-blue-200">Due now (will be dispatched by scheduler)</h2>
        <table class="admin-table">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Event</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Channel</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Scheduled at</th>
                </tr>
            </thead>
            <tbody>
                @foreach($due as $m)
                <tr>
                    <td class="px-6 py-4">
                        <a href="{{ route('events.show', $m->event) }}" class="admin-link">{{ $m->event->name }}</a>
                    </td>
                    <td class="px-6 py-4 text-sm text-ink/65">{{ $m->channel->name }}</td>
                    <td class="px-6 py-4 text-sm text-ink/65">{{ $m->scheduled_at?->format('M j, Y H:i') ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if($pending->isEmpty() && $due->isEmpty())
    <div class="admin-card p-12 text-center text-ink/50">
        No pending or due messages. Create and schedule messages from an event's Messages page.
    </div>
    @endif
</div>

<div class="mt-8 p-4 rounded-lg bg-dawn-2/30 text-sm text-ink/65">
    <p class="font-medium text-ink/75">Cron setup</p>
    <p class="mt-1">Add to crontab: <code class="bg-dawn-2 px-1 rounded">* * * * * cd {{ base_path() }} && php artisan schedule:run >> /dev/null 2>&1</code></p>
    <p class="mt-2">Run queue worker: <code class="bg-dawn-2 px-1 rounded">php artisan queue:work</code></p>
</div>
@endsection

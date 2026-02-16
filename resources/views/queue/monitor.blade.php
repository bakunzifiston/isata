@extends('layouts.dashboard')

@section('title', 'Queue Status - ' . config('app.name'))

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-900">Queue status monitor</h1>
    <p class="mt-1 text-slate-600">Pending messages and queue health</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
        <p class="text-sm font-medium text-slate-500 uppercase">Pending (scheduled)</p>
        <p class="mt-2 text-3xl font-bold text-slate-900">{{ $pending->count() }}</p>
        <p class="mt-1 text-sm text-slate-600">Messages waiting to be sent</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
        <p class="text-sm font-medium text-slate-500 uppercase">Due now</p>
        <p class="mt-2 text-3xl font-bold text-blue-600">{{ $due->count() }}</p>
        <p class="mt-1 text-sm text-slate-600">Ready for scheduler to dispatch</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
        <p class="text-sm font-medium text-slate-500 uppercase">Queue jobs</p>
        <p class="mt-2 text-3xl font-bold text-slate-900">{{ $jobsCount }}</p>
        <p class="mt-1 text-sm text-slate-600">Jobs in queue · {{ $failedCount }} failed</p>
    </div>
</div>

<div class="space-y-6">
    @if($pending->isNotEmpty())
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <h2 class="px-6 py-4 text-sm font-medium text-slate-700 bg-slate-50 border-b border-slate-200">Scheduled messages</h2>
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Event</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Channel</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Scheduled at</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @foreach($pending as $m)
                <tr class="hover:bg-slate-50">
                    <td class="px-6 py-4">
                        <a href="{{ route('events.show', $m->event) }}" class="text-indigo-600 hover:text-indigo-900">{{ $m->event->name }}</a>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-600">{{ $m->channel->name }}</td>
                    <td class="px-6 py-4 text-sm text-slate-600">{{ $m->scheduled_at?->format('M j, Y H:i') ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if($due->isNotEmpty())
    <div class="bg-white rounded-xl border border-blue-200 shadow-sm overflow-hidden">
        <h2 class="px-6 py-4 text-sm font-medium text-blue-800 bg-blue-50 border-b border-blue-200">Due now (will be dispatched by scheduler)</h2>
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Event</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Channel</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Scheduled at</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @foreach($due as $m)
                <tr class="hover:bg-slate-50">
                    <td class="px-6 py-4">
                        <a href="{{ route('events.show', $m->event) }}" class="text-indigo-600 hover:text-indigo-900">{{ $m->event->name }}</a>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-600">{{ $m->channel->name }}</td>
                    <td class="px-6 py-4 text-sm text-slate-600">{{ $m->scheduled_at?->format('M j, Y H:i') ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if($pending->isEmpty() && $due->isEmpty())
    <div class="bg-white rounded-xl border border-slate-200 p-12 text-center text-slate-500">
        No pending or due messages. Create and schedule messages from an event's Messages page.
    </div>
    @endif
</div>

<div class="mt-8 p-4 rounded-lg bg-slate-50 text-sm text-slate-600">
    <p class="font-medium text-slate-700">Cron setup</p>
    <p class="mt-1">Add to crontab: <code class="bg-slate-200 px-1 rounded">* * * * * cd {{ base_path() }} && php artisan schedule:run >> /dev/null 2>&1</code></p>
    <p class="mt-2">Run queue worker: <code class="bg-slate-200 px-1 rounded">php artisan queue:work</code></p>
</div>
@endsection

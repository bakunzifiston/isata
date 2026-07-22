@extends('layouts.dashboard')

@section('title', 'Event Report - ' . $event->name . ' - ' . config('app.name'))

@section('content')
<div class="mb-8">
    <a href="{{ route('analytics.index') }}" class="text-ink/65 hover:text-ink">← Analytics</a>
    <h1 class="admin-page-title mt-2">{{ $event->name }} — Performance report</h1>
    <p class="admin-page-subtitle">{{ $event->date->format('l, F j, Y') }}@if($event->time_formatted) at {{ $event->time_formatted }}@endif</p>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-xl border border-dawn-2/80 p-4 shadow-sm">
        <p class="text-xs font-medium text-ink/50 uppercase">Total attendees</p>
        <p class="admin-stat-value">{{ $metrics['total_attendees'] }}</p>
    </div>
    <div class="bg-white rounded-xl border border-dawn-2/80 p-4 shadow-sm">
        <p class="text-xs font-medium text-ink/50 uppercase">Responded</p>
        <p class="mt-1 text-2xl font-bold text-coral">{{ $metrics['responded'] }}</p>
    </div>
    <div class="bg-white rounded-xl border border-dawn-2/80 p-4 shadow-sm">
        <p class="text-xs font-medium text-ink/50 uppercase">Attended</p>
        <p class="mt-1 text-2xl font-bold text-emerald-600">{{ $metrics['attended'] }}</p>
    </div>
    <div class="bg-white rounded-xl border border-dawn-2/80 p-4 shadow-sm">
        <p class="text-xs font-medium text-ink/50 uppercase">Messages sent</p>
        <p class="admin-stat-value">{{ $metrics['messages_sent'] }}</p>
    </div>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-xl border border-dawn-2/80 p-4 shadow-sm">
        <p class="text-xs font-medium text-ink/50 uppercase">RSVP rate</p>
        <p class="mt-1 text-2xl font-bold text-coral">{{ $metrics['rsvp_rate'] }}%</p>
    </div>
    <div class="bg-white rounded-xl border border-dawn-2/80 p-4 shadow-sm">
        <p class="text-xs font-medium text-ink/50 uppercase">Attendance %</p>
        <p class="mt-1 text-2xl font-bold text-emerald-600">{{ $metrics['attendance_rate'] }}%</p>
    </div>
    <div class="bg-white rounded-xl border border-dawn-2/80 p-4 shadow-sm">
        <p class="text-xs font-medium text-ink/50 uppercase">Delivery rate</p>
        <p class="mt-1 text-2xl font-bold text-blue-600">{{ $metrics['delivery_rate'] }}%</p>
    </div>
    <div class="bg-white rounded-xl border border-dawn-2/80 p-4 shadow-sm">
        <p class="text-xs font-medium text-ink/50 uppercase">Open rate</p>
        <p class="mt-1 text-2xl font-bold text-amber-600">{{ $metrics['open_rate'] }}%</p>
    </div>
</div>

<div class="admin-table-wrap">
    <h2 class="px-6 py-4 text-sm font-medium text-ink/75 bg-dawn-2/30 border-b border-dawn-2/80">Recent communication logs</h2>
    @if($logs->isEmpty())
    <p class="px-6 py-8 text-ink/50 text-center">No communication logs for this event yet.</p>
    @else
    <table class="admin-table">
        <thead>
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Attendee</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Channel</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Sent</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $log)
            <tr>
                <td class="px-6 py-4 font-medium text-ink">{{ $log->attendee->name ?? '—' }}</td>
                <td class="px-6 py-4 text-sm text-ink/65">{{ $log->channel->name ?? '—' }}</td>
                <td class="px-6 py-4 text-sm text-ink/65">{{ $log->sent_at->format('M j, H:i') }}</td>
                <td class="px-6 py-4">
                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full
                        {{ $log->status === 'delivered' ? 'bg-emerald-100 text-emerald-800' : '' }}
                        {{ $log->status === 'sent' ? 'bg-blue-100 text-blue-800' : '' }}
                        {{ $log->status === 'failed' ? 'bg-red-100 text-red-800' : '' }}
                    ">{{ ucfirst($log->status) }}</span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>
@endsection

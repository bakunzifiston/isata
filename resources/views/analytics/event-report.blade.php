@extends('layouts.dashboard')

@section('title', 'Event Report - ' . $event->name . ' - ' . config('app.name'))

@section('content')
<div class="mb-8">
    <a href="{{ route('analytics.index') }}" class="text-slate-600 hover:text-slate-900">← Analytics</a>
    <h1 class="text-2xl font-bold text-slate-900 mt-2">{{ $event->name }} — Performance report</h1>
    <p class="mt-1 text-slate-600">{{ $event->date->format('l, F j, Y') }}@if($event->time_formatted) at {{ $event->time_formatted }}@endif</p>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
        <p class="text-xs font-medium text-slate-500 uppercase">Total attendees</p>
        <p class="mt-1 text-2xl font-bold text-slate-900">{{ $metrics['total_attendees'] }}</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
        <p class="text-xs font-medium text-slate-500 uppercase">Responded</p>
        <p class="mt-1 text-2xl font-bold text-indigo-600">{{ $metrics['responded'] }}</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
        <p class="text-xs font-medium text-slate-500 uppercase">Attended</p>
        <p class="mt-1 text-2xl font-bold text-emerald-600">{{ $metrics['attended'] }}</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
        <p class="text-xs font-medium text-slate-500 uppercase">Messages sent</p>
        <p class="mt-1 text-2xl font-bold text-slate-900">{{ $metrics['messages_sent'] }}</p>
    </div>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
        <p class="text-xs font-medium text-slate-500 uppercase">RSVP rate</p>
        <p class="mt-1 text-2xl font-bold text-indigo-600">{{ $metrics['rsvp_rate'] }}%</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
        <p class="text-xs font-medium text-slate-500 uppercase">Attendance %</p>
        <p class="mt-1 text-2xl font-bold text-emerald-600">{{ $metrics['attendance_rate'] }}%</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
        <p class="text-xs font-medium text-slate-500 uppercase">Delivery rate</p>
        <p class="mt-1 text-2xl font-bold text-blue-600">{{ $metrics['delivery_rate'] }}%</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
        <p class="text-xs font-medium text-slate-500 uppercase">Open rate</p>
        <p class="mt-1 text-2xl font-bold text-amber-600">{{ $metrics['open_rate'] }}%</p>
    </div>
</div>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <h2 class="px-6 py-4 text-sm font-medium text-slate-700 bg-slate-50 border-b border-slate-200">Recent communication logs</h2>
    @if($logs->isEmpty())
    <p class="px-6 py-8 text-slate-500 text-center">No communication logs for this event yet.</p>
    @else
    <table class="min-w-full divide-y divide-slate-200">
        <thead class="bg-slate-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Attendee</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Channel</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Sent</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            @foreach($logs as $log)
            <tr class="hover:bg-slate-50">
                <td class="px-6 py-4 font-medium text-slate-900">{{ $log->attendee->name ?? '—' }}</td>
                <td class="px-6 py-4 text-sm text-slate-600">{{ $log->channel->name ?? '—' }}</td>
                <td class="px-6 py-4 text-sm text-slate-600">{{ $log->sent_at->format('M j, H:i') }}</td>
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

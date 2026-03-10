@extends('layouts.super-admin')

@section('title', 'Events')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-900">All Events</h1>
    <p class="mt-1 text-slate-600">Events across the platform</p>
</div>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Event</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Organization</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Attendees</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($events as $event)
                <tr class="hover:bg-slate-50">
                    <td class="px-6 py-4 font-medium text-slate-900">{{ $event->name }}</td>
                    <td class="px-6 py-4 text-sm text-slate-600">{{ $event->organization?->name ?? '—' }}</td>
                    <td class="px-6 py-4 text-sm text-slate-600">{{ $event->date?->format('M j, Y') }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-0.5 rounded text-xs font-medium
                            {{ $event->status === 'scheduled' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $event->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : '' }}
                            {{ $event->status === 'draft' ? 'bg-amber-100 text-amber-800' : '' }}
                            {{ $event->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}
                        ">{{ ucfirst($event->status) }}</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-600">{{ $event->attendees_count }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-6 py-12 text-center text-slate-500">No events</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($events->hasPages())
    <div class="px-6 py-4 border-t border-slate-200">{{ $events->links() }}</div>
    @endif
</div>
@endsection

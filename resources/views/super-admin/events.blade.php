@extends('layouts.super-admin')

@section('title', 'Events')

@section('content')
<div class="mb-8">
    <h1 class="admin-page-title">All Events</h1>
    <p class="admin-page-subtitle">Events across the platform</p>
</div>

<div class="admin-table-wrap">
    <div class="overflow-x-auto">
        <table class="admin-table">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Event</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Organization</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Attendees</th>
                </tr>
            </thead>
            <tbody>
                @forelse($events as $event)
                <tr>
                    <td class="px-6 py-4 font-medium text-ink">{{ $event->name }}</td>
                    <td class="px-6 py-4 text-sm text-ink/65">{{ $event->organization?->name ?? '—' }}</td>
                    <td class="px-6 py-4 text-sm text-ink/65">{{ $event->date?->format('M j, Y') }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-0.5 rounded text-xs font-medium
                            {{ $event->status === 'scheduled' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $event->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : '' }}
                            {{ $event->status === 'draft' ? 'bg-amber-100 text-amber-800' : '' }}
                            {{ $event->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}
                        ">{{ ucfirst($event->status) }}</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-ink/65">{{ $event->attendees_count }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-6 py-12 text-center text-ink/50">No events</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($events->hasPages())
    <div class="px-6 py-4 border-t border-dawn-2/80">{{ $events->links() }}</div>
    @endif
</div>
@endsection

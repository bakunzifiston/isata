@extends('layouts.dashboard')

@section('title', 'Calendar - ' . config('app.name'))

@section('content')
<div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="admin-page-title">Event calendar</h1>
        <p class="admin-page-subtitle">View and manage your events</p>
    </div>
    <a href="{{ route('events.create') }}" class="admin-btn-primary inline-flex items-center gap-2">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Create event
    </a>
</div>

<x-admin.card class="overflow-hidden p-6">
    <div id="calendar" class="fc fc-media-screen"></div>
</x-admin.card>

<style>
.fc { font-family: inherit; }
.fc .fc-toolbar-title { font-size: 1.25rem; font-weight: 600; color: #1a1a1a; }
.fc .fc-button { background: #E8604C; border-color: #E8604C; }
.fc .fc-button:hover { background: #d45542; border-color: #d45542; }
.fc .fc-button-primary:not(:disabled).fc-button-active { background: #d45542; }
.fc-event { border-radius: 6px; }
</style>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listWeek'
        },
        events: '{{ route("events.calendar.data") }}',
        eventClick: function(info) {
            info.jsEvent.preventDefault();
            if (info.event.url) {
                window.location.href = info.event.url;
            }
        },
        height: 'auto'
    });
    calendar.render();
});
</script>
@endpush

@extends('layouts.dashboard')

@section('title', 'Calendar - ' . config('app.name'))

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Event calendar</h1>
        <p class="mt-1 text-slate-600">View and manage your events</p>
    </div>
    <a href="{{ route('events.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 text-white font-medium hover:bg-indigo-700 shadow-sm">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Create event
    </a>
</div>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="p-6">
        <div id="calendar" class="fc fc-media-screen"></div>
    </div>
</div>

<style>
.fc { font-family: inherit; }
.fc .fc-toolbar-title { font-size: 1.25rem; font-weight: 600; }
.fc .fc-button { background: #6366f1; border-color: #6366f1; }
.fc .fc-button:hover { background: #4f46e5; border-color: #4f46e5; }
.fc .fc-button-primary:not(:disabled).fc-button-active { background: #4f46e5; }
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

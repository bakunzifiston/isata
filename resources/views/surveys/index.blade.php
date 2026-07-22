@extends('layouts.dashboard')

@section('title', 'Surveys - ' . config('app.name'))

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
    <div>
        <h1 class="admin-page-title">Post-event surveys</h1>
        <p class="admin-page-subtitle">Feedback surveys, thank-you messages, and reports</p>
    </div>
    <a href="{{ route('surveys.create') }}" class="admin-btn-primary">
        Create survey
    </a>
</div>

<form method="GET" action="{{ route('surveys.index') }}" class="mb-6">
    <label for="event_id" class="admin-label mb-2">Filter by event</label>
    <select name="event_id" id="event_id" onchange="this.form.submit()" class="admin-input">
        <option value="">All events</option>
        @foreach($events as $e)
        <option value="{{ $e->id }}" {{ request('event_id') == $e->id ? 'selected' : '' }}>
            {{ $e->name }} ({{ $e->date->format('M j, Y') }})
        </option>
        @endforeach
    </select>
</form>

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Survey</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Event</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Responses</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-ink/50">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($surveys as $survey)
            <tr>
                <td class="px-6 py-4 font-medium text-ink">{{ $survey->name }}</td>
                <td class="px-6 py-4 text-sm text-ink/65">{{ $survey->event->name }}</td>
                <td class="px-6 py-4 text-sm text-ink/65">{{ $survey->feedback->count() }}</td>
                <td class="px-6 py-4">
                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full {{ $survey->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-dawn-2 text-ink/70' }}">
                        {{ $survey->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td class="px-6 py-4 text-right text-sm space-x-2">
                    <a href="{{ route('surveys.responses', $survey) }}" class="admin-link">Responses</a>
                    <a href="{{ route('surveys.report', $survey) }}" class="admin-link">Report</a>
                    <a href="{{ route('surveys.edit', $survey) }}" class="admin-link">Edit</a>
                    <form method="POST" action="{{ route('surveys.destroy', $survey) }}" class="inline" onsubmit="return confirm('Delete this survey?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-6 py-12 text-center text-ink/50">
                    No surveys yet. <a href="{{ route('surveys.create') }}" class="admin-link">Create your first survey</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($surveys->hasPages())
    <div class="px-6 py-4 border-t border-dawn-2/80">{{ $surveys->links() }}</div>
    @endif
</div>
@endsection

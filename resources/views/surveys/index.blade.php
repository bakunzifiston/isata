@extends('layouts.dashboard')

@section('title', 'Surveys - ' . config('app.name'))

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Post-event surveys</h1>
        <p class="mt-1 text-slate-600">Feedback surveys, thank-you messages, and reports</p>
    </div>
    <a href="{{ route('surveys.create') }}" class="px-4 py-2 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-700">
        Create survey
    </a>
</div>

<form method="GET" action="{{ route('surveys.index') }}" class="mb-6">
    <label for="event_id" class="block text-sm font-medium text-slate-700 mb-2">Filter by event</label>
    <select name="event_id" id="event_id" onchange="this.form.submit()" class="px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500">
        <option value="">All events</option>
        @foreach($events as $e)
        <option value="{{ $e->id }}" {{ request('event_id') == $e->id ? 'selected' : '' }}>
            {{ $e->name }} ({{ $e->date->format('M j, Y') }})
        </option>
        @endforeach
    </select>
</form>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <table class="min-w-full divide-y divide-slate-200">
        <thead class="bg-slate-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Survey</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Event</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Responses</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            @forelse($surveys as $survey)
            <tr class="hover:bg-slate-50">
                <td class="px-6 py-4 font-medium text-slate-900">{{ $survey->name }}</td>
                <td class="px-6 py-4 text-sm text-slate-600">{{ $survey->event->name }}</td>
                <td class="px-6 py-4 text-sm text-slate-600">{{ $survey->feedback->count() }}</td>
                <td class="px-6 py-4">
                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full {{ $survey->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-800' }}">
                        {{ $survey->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td class="px-6 py-4 text-right text-sm space-x-2">
                    <a href="{{ route('surveys.responses', $survey) }}" class="text-indigo-600 hover:text-indigo-900">Responses</a>
                    <a href="{{ route('surveys.report', $survey) }}" class="text-indigo-600 hover:text-indigo-900">Report</a>
                    <a href="{{ route('surveys.edit', $survey) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                    <form method="POST" action="{{ route('surveys.destroy', $survey) }}" class="inline" onsubmit="return confirm('Delete this survey?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                    No surveys yet. <a href="{{ route('surveys.create') }}" class="text-indigo-600 hover:text-indigo-900">Create your first survey</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($surveys->hasPages())
    <div class="px-6 py-4 border-t border-slate-200">{{ $surveys->links() }}</div>
    @endif
</div>
@endsection

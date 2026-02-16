@extends('layouts.dashboard')

@section('title', 'Survey Responses - ' . $survey->name . ' - ' . config('app.name'))

@section('content')
<div class="mb-8">
    <a href="{{ route('surveys.index') }}" class="text-slate-600 hover:text-slate-900">← Surveys</a>
    <h1 class="text-2xl font-bold text-slate-900 mt-2">{{ $survey->name }} — Responses</h1>
    <p class="mt-1 text-slate-600">{{ $survey->event->name }} · {{ $feedback->total() }} response(s)</p>
</div>

<div class="flex gap-4 mb-6">
    <a href="{{ route('surveys.report', $survey) }}" class="px-4 py-2 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-700">View report</a>
</div>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <table class="min-w-full divide-y divide-slate-200">
        <thead class="bg-slate-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Attendee</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Submitted</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Responses</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            @forelse($feedback as $fb)
            <tr class="hover:bg-slate-50">
                <td class="px-6 py-4 font-medium text-slate-900">{{ $fb->attendee->name }}</td>
                <td class="px-6 py-4 text-sm text-slate-600">{{ $fb->submitted_at->format('M j, Y H:i') }}</td>
                <td class="px-6 py-4 text-sm">
                    <div class="space-y-1 max-w-md">
                        <a href="{{ route('certificates.show', $fb) }}" target="_blank" class="text-indigo-600 hover:text-indigo-900 text-xs">View certificate</a>
                        @foreach($survey->questions as $q)
                        <div>
                            <span class="text-slate-500">{{ $q['label'] }}:</span>
                            <span class="text-slate-900">
                                @if(is_array($fb->responses[$q['id']] ?? null))
                                    {{ implode(', ', $fb->responses[$q['id']]) }}
                                @else
                                    {{ $fb->responses[$q['id']] ?? '—' }}
                                @endif
                            </span>
                        </div>
                        @endforeach
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="3" class="px-6 py-12 text-center text-slate-500">No responses yet. Share the {feedback_link} in your messages.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($feedback->hasPages())
    <div class="px-6 py-4 border-t border-slate-200">{{ $feedback->links() }}</div>
    @endif
</div>
@endsection

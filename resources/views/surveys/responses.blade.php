@extends('layouts.dashboard')

@section('title', 'Survey Responses - ' . $survey->name . ' - ' . config('app.name'))

@section('content')
<div class="mb-8">
    <a href="{{ route('surveys.index') }}" class="text-ink/65 hover:text-ink">← Surveys</a>
    <h1 class="admin-page-title mt-2">{{ $survey->name }} — Responses</h1>
    <p class="admin-page-subtitle">{{ $survey->event->name }} · {{ $feedback->total() }} response(s)</p>
</div>

<div class="flex gap-4 mb-6">
    <a href="{{ route('surveys.report', $survey) }}" class="admin-btn-primary">View report</a>
</div>

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Attendee</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Submitted</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Responses</th>
            </tr>
        </thead>
        <tbody>
            @forelse($feedback as $fb)
            <tr>
                <td class="px-6 py-4 font-medium text-ink">{{ $fb->attendee->name }}</td>
                <td class="px-6 py-4 text-sm text-ink/65">{{ $fb->submitted_at->format('M j, Y H:i') }}</td>
                <td class="px-6 py-4 text-sm">
                    <div class="space-y-1 max-w-md">
                        <a href="{{ route('certificates.show', $fb) }}" target="_blank" class="admin-link text-xs">View certificate</a>
                        @foreach($survey->questions as $q)
                        <div>
                            <span class="text-ink/50">{{ $q['label'] }}:</span>
                            <span class="text-ink">
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
                <td colspan="3" class="px-6 py-12 text-center text-ink/50">No responses yet. Share the {feedback_link} in your messages.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($feedback->hasPages())
    <div class="px-6 py-4 border-t border-dawn-2/80">{{ $feedback->links() }}</div>
    @endif
</div>
@endsection

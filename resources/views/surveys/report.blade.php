@extends('layouts.dashboard')

@section('title', 'Survey Report - ' . $survey->name . ' - ' . config('app.name'))

@section('content')
<div class="mb-8">
    <a href="{{ route('surveys.index') }}" class="text-slate-600 hover:text-slate-900">← Surveys</a>
    <h1 class="text-2xl font-bold text-slate-900 mt-2">{{ $survey->name }} — Report</h1>
    <p class="mt-1 text-slate-600">{{ $survey->event->name }} · {{ $feedback->count() }} response(s)</p>
</div>

<div class="space-y-6">
    @foreach($summary as $qId => $data)
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">{{ $data['label'] }}</h2>
        @if($data['type'] === 'rating')
        <div class="flex items-center gap-4">
            <div class="text-3xl font-bold text-indigo-600">{{ $data['answers']['avg'] ?? 0 }}</div>
            <span class="text-slate-500">average</span>
        </div>
        @if(!empty($data['answers']['distribution']))
        <div class="mt-4 flex gap-2">
            @foreach($data['answers']['distribution'] as $rating => $count)
            <div class="flex-1 text-center p-2 bg-slate-50 rounded">
                <span class="font-medium">{{ $rating }}</span>
                <span class="block text-sm text-slate-500">{{ $count }}</span>
            </div>
            @endforeach
        </div>
        @endif
        @else
        <div class="space-y-2">
            @foreach($data['answers'] as $answer => $count)
            <div class="flex justify-between items-center">
                <span class="text-slate-700">{{ $answer ?: '(empty)' }}</span>
                <span class="font-medium text-slate-900">{{ $count }}</span>
            </div>
            @endforeach
            @if(empty($data['answers']))
            <p class="text-slate-500">No responses yet</p>
            @endif
        </div>
        @endif
    </div>
    @endforeach
</div>

<a href="{{ route('surveys.responses', $survey) }}" class="inline-block mt-6 text-sm text-slate-600 hover:text-slate-900">View all responses →</a>
@endsection

<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use App\Models\Event;
use App\Models\Survey;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SurveyController extends Controller
{
    public function index(Request $request): View
    {
        $organization = auth()->user()->organization;

        if (! $organization) {
            abort(403);
        }

        $eventId = $request->input('event_id');
        $query = Survey::where('organization_id', $organization->id)
            ->with(['event', 'feedback'])
            ->orderByDesc('created_at');

        if ($eventId) {
            $query->where('event_id', $eventId);
        }

        $surveys = $query->paginate(15);
        $events = $organization->events()
            ->whereIn('status', [Event::STATUS_SCHEDULED, Event::STATUS_COMPLETED])
            ->orderByDesc('date')
            ->get();

        return view('surveys.index', [
            'surveys' => $surveys,
            'events' => $events,
        ]);
    }

    public function create(Request $request): View
    {
        $organization = auth()->user()->organization;

        if (! $organization) {
            abort(403);
        }

        $events = $organization->events()
            ->whereIn('status', [Event::STATUS_SCHEDULED, Event::STATUS_COMPLETED])
            ->orderByDesc('date')
            ->get();

        return view('surveys.create', [
            'events' => $events,
            'preselectedEventId' => $request->input('event_id'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $organization = auth()->user()->organization;

        if (! $organization) {
            abort(403);
        }

        $validated = $request->validate([
            'event_id' => ['required', 'exists:events,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'questions' => ['required', 'array'],
            'questions.*.id' => ['required', 'string'],
            'questions.*.type' => ['required', 'in:text,rating,select,multiple'],
            'questions.*.label' => ['required', 'string', 'max:500'],
            'questions.*.options' => ['nullable'],
            'questions.*.required' => ['nullable', 'boolean'],
            'thank_you_message' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $event = Event::findOrFail($validated['event_id']);
        if ($event->organization_id !== $organization->id) {
            abort(403);
        }

        $organization->surveys()->create([
            'event_id' => $validated['event_id'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'questions' => array_map(function ($q) {
                $opts = $q['options'] ?? [];
                if (is_string($opts)) {
                    $opts = array_filter(array_map('trim', explode("\n", $opts)));
                }
                return [
                    'id' => $q['id'],
                    'type' => $q['type'],
                    'label' => $q['label'],
                    'options' => array_values($opts),
                    'required' => ! empty($q['required']),
                ];
            }, $validated['questions']),
            'thank_you_message' => $validated['thank_you_message'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()->route('surveys.index')->with('status', 'Survey created.');
    }

    public function edit(Survey $survey): View
    {
        $organization = auth()->user()->organization;

        if (! $organization || $survey->organization_id !== $organization->id) {
            abort(404);
        }

        $events = $organization->events()
            ->whereIn('status', [Event::STATUS_SCHEDULED, Event::STATUS_COMPLETED])
            ->orderByDesc('date')
            ->get();

        return view('surveys.edit', [
            'survey' => $survey,
            'events' => $events,
        ]);
    }

    public function update(Request $request, Survey $survey): RedirectResponse
    {
        $organization = auth()->user()->organization;

        if (! $organization || $survey->organization_id !== $organization->id) {
            abort(403);
        }

        $validated = $request->validate([
            'event_id' => ['required', 'exists:events,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'questions' => ['required', 'array'],
            'questions.*.id' => ['required', 'string'],
            'questions.*.type' => ['required', 'in:text,rating,select,multiple'],
            'questions.*.label' => ['required', 'string', 'max:500'],
            'questions.*.options' => ['nullable'],
            'questions.*.required' => ['nullable', 'boolean'],
            'thank_you_message' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $survey->update([
            'event_id' => $validated['event_id'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'questions' => array_map(function ($q) {
                $opts = $q['options'] ?? [];
                if (is_string($opts)) {
                    $opts = array_filter(array_map('trim', explode("\n", $opts)));
                }
                return [
                    'id' => $q['id'],
                    'type' => $q['type'],
                    'label' => $q['label'],
                    'options' => array_values($opts),
                    'required' => ! empty($q['required']),
                ];
            }, $validated['questions']),
            'thank_you_message' => $validated['thank_you_message'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()->route('surveys.index')->with('status', 'Survey updated.');
    }

    public function destroy(Survey $survey): RedirectResponse
    {
        $organization = auth()->user()->organization;

        if (! $organization || $survey->organization_id !== $organization->id) {
            abort(403);
        }

        $survey->delete();

        return redirect()->route('surveys.index')->with('status', 'Survey deleted.');
    }

    public function responses(Survey $survey): View
    {
        $organization = auth()->user()->organization;

        if (! $organization || $survey->organization_id !== $organization->id) {
            abort(404);
        }

        $feedback = $survey->feedback()->with('attendee')->orderByDesc('submitted_at')->paginate(20);

        return view('surveys.responses', [
            'survey' => $survey,
            'feedback' => $feedback,
        ]);
    }

    public function report(Survey $survey): View
    {
        $organization = auth()->user()->organization;

        if (! $organization || $survey->organization_id !== $organization->id) {
            abort(404);
        }

        $feedback = $survey->feedback()->with('attendee')->get();

        $summary = [];
        foreach ($survey->questions as $q) {
            $answers = $feedback->pluck('responses.' . $q['id'])->filter();
            $values = $answers->flatten()->filter();
            $summary[$q['id']] = [
                'label' => $q['label'],
                'type' => $q['type'],
                'count' => $values->count(),
                'answers' => $q['type'] === 'rating' ? [
                    'avg' => $values->count() ? round($values->avg(), 1) : 0,
                    'distribution' => $values->countBy()->sortKeys()->toArray(),
                ] : $values->countBy()->toArray(),
            ];
        }

        return view('surveys.report', [
            'survey' => $survey,
            'feedback' => $feedback,
            'summary' => $summary,
        ]);
    }
}

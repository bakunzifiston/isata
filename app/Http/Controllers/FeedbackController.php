<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use App\Models\Event;
use App\Models\Feedback;
use App\Models\Survey;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    public function show(Event $event, Attendee $attendee, Request $request): View|RedirectResponse
    {
        if ($attendee->event_id !== $event->id) {
            abort(404);
        }

        if (! $request->hasValidSignature()) {
            abort(403, 'Invalid or expired feedback link.');
        }

        $survey = $event->surveys()->where('is_active', true)->first();

        if (! $survey) {
            return redirect()->route('feedback.thank-you')->with('message', 'No survey available for this event.');
        }

        $existing = Feedback::where('survey_id', $survey->id)->where('attendee_id', $attendee->id)->first();
        if ($existing) {
            return redirect()->route('feedback.thank-you')->with('message', 'You have already submitted feedback.');
        }

        return view('feedback.show', [
            'event' => $event,
            'attendee' => $attendee,
            'survey' => $survey,
        ]);
    }

    public function store(Request $request, Event $event, Attendee $attendee): RedirectResponse
    {
        $validated = $request->validate([
            'responses' => ['required', 'array'],
        ]);

        if ($attendee->event_id !== $event->id) {
            abort(404);
        }

        $survey = $event->surveys()->where('is_active', true)->first();
        if (! $survey) {
            return redirect()->route('feedback.thank-you')->with('message', 'No survey available.');
        }

        $existing = Feedback::where('survey_id', $survey->id)->where('attendee_id', $attendee->id)->first();
        if ($existing) {
            return redirect()->route('feedback.thank-you')->with('message', 'You have already submitted feedback.');
        }

        Feedback::create([
            'survey_id' => $survey->id,
            'attendee_id' => $attendee->id,
            'event_id' => $event->id,
            'responses' => $validated['responses'],
            'submitted_at' => now(),
        ]);

        return redirect()->route('feedback.thank-you')->with('message', $survey->thank_you_message ?: 'Thank you for your feedback!');
    }

    public function thankYou(): View
    {
        return view('feedback.thank-you');
    }
}

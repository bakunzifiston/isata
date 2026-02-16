<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use App\Models\Event;
use App\Models\Rsvp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RsvpController extends Controller
{
    public function show(Event $event, Attendee $attendee, Request $request): View|RedirectResponse
    {
        if ($attendee->event_id !== $event->id) {
            abort(404);
        }

        if (! $request->hasValidSignature()) {
            abort(403, 'Invalid or expired RSVP link.');
        }

        return view('rsvp.respond', [
            'event' => $event,
            'attendee' => $attendee,
        ]);
    }

    public function store(Request $request, Event $event, Attendee $attendee): RedirectResponse
    {
        $validated = $request->validate([
            'response' => ['required', 'in:Yes,No,Maybe'],
            'response_channel' => ['nullable', 'string', 'in:email,sms,web'],
        ]);

        if ($attendee->event_id !== $event->id) {
            abort(404);
        }

        Rsvp::create([
            'attendee_id' => $attendee->id,
            'event_id' => $event->id,
            'response' => $validated['response'],
            'response_channel' => $validated['response_channel'] ?? 'web',
            'responded_at' => now(),
        ]);

        $attendee->update([
            'rsvp_status' => Rsvp::mapToAttendeeStatus($validated['response']),
        ]);

        return redirect()->route('rsvp.thank-you')
            ->with('status', 'Thank you! Your response has been recorded.');
    }

    public function eventLanding(Event $event): View
    {
        return view('rsvp.event-landing', ['event' => $event]);
    }

    public function lookup(Request $request): \Illuminate\Http\RedirectResponse|View
    {
        $eventId = $request->input('event');
        $email = $request->input('email');

        if (! $eventId || ! $email) {
            return redirect()->back()->with('error', 'Email required.');
        }

        $attendee = Attendee::where('event_id', $eventId)->where('email', $email)->first();

        if ($attendee) {
            return redirect()->away(
                \Illuminate\Support\Facades\URL::signedRoute('rsvp.show', [
                    'event' => $attendee->event,
                    'attendee' => $attendee,
                ], now()->addDays(30))
            );
        }

        return view('rsvp.event-landing', [
            'event' => Event::findOrFail($eventId),
            'error' => 'No invitation found for that email.',
        ]);
    }

    public function thankYou(): View
    {
        return view('rsvp.thank-you');
    }

    public function smsWebhook(Request $request)
    {
        $body = $request->input('Body', $request->input('body', ''));
        $from = $request->input('From', $request->input('from', ''));

        $parsed = $this->parseSmsReply($body);
        if (! $parsed) {
            return response()->json(['message' => 'Could not parse response'], 400);
        }

        $digits = preg_replace('/\D/', '', $from);
        $attendee = Attendee::query()
            ->where(function ($q) use ($digits, $from) {
                $q->where('phone', 'like', '%' . substr($digits, -10) . '%')
                    ->orWhere('email', $from);
            })
            ->orderByDesc('created_at')
            ->first();

        if (! $attendee) {
            return response()->json(['message' => 'Attendee not found'], 404);
        }

        Rsvp::create([
            'attendee_id' => $attendee->id,
            'event_id' => $attendee->event_id,
            'response' => $parsed['response'],
            'response_channel' => Rsvp::CHANNEL_SMS,
            'responded_at' => now(),
        ]);

        $attendee->update([
            'rsvp_status' => Rsvp::mapToAttendeeStatus($parsed['response']),
        ]);

        return response()->json(['message' => 'RSVP recorded']);
    }

    protected function parseSmsReply(string $body): ?array
    {
        $body = trim(strtolower($body));

        if (in_array($body, ['yes', 'y', '1', 'confirm', 'confirmed', 'attending'])) {
            return ['response' => Rsvp::RESPONSE_YES];
        }
        if (in_array($body, ['no', 'n', '0', 'decline', 'declined', 'not attending', 'cant', "can't"])) {
            return ['response' => Rsvp::RESPONSE_NO];
        }
        if (in_array($body, ['maybe', 'm', 'unsure', 'tbd'])) {
            return ['response' => Rsvp::RESPONSE_MAYBE];
        }

        return null;
    }
}

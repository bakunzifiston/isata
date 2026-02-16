<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use App\Models\BeepCall;
use App\Models\Event;
use App\Jobs\PlaceBeepCallJob;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BeepCallController extends Controller
{
    protected function ensurePremium(): void
    {
        $organization = auth()->user()->organization;
        $plan = $organization?->subscriptionPlan;

        if (! $organization || ! $plan?->hasBeepCalls()) {
            abort(403, 'Beep calls require a Premium subscription. Please upgrade.');
        }
    }

    public function index(Request $request): View
    {
        $this->ensurePremium();

        $organization = auth()->user()->organization;
        $eventId = $request->input('event_id');

        $query = BeepCall::where('organization_id', $organization->id)
            ->with(['event', 'attendee'])
            ->orderByDesc('call_schedule');

        if ($eventId) {
            $query->where('event_id', $eventId);
        }

        $calls = $query->paginate(15);
        $events = $organization->events()
            ->whereIn('status', [Event::STATUS_SCHEDULED, Event::STATUS_COMPLETED])
            ->orderByDesc('date')
            ->get();

        return view('beep-calls.index', [
            'calls' => $calls,
            'events' => $events,
        ]);
    }

    public function create(Request $request): View
    {
        $this->ensurePremium();

        $organization = auth()->user()->organization;

        $events = $organization->events()
            ->whereIn('status', [Event::STATUS_SCHEDULED, Event::STATUS_COMPLETED])
            ->orderByDesc('date')
            ->get();

        $eventId = $request->input('event_id');
        $attendees = collect();
        if ($eventId) {
            $attendees = Attendee::where('event_id', $eventId)->where(function ($q) {
                $q->whereNotNull('phone')->orWhereNotNull('email');
            })->get();
        }

        return view('beep-calls.create', [
            'events' => $events,
            'attendees' => $attendees,
            'preselectedEventId' => $eventId,
        ]);
    }

    public function uploadAudio(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->ensurePremium();

        $request->validate(['audio' => ['required', 'file', 'mimes:mp3,wav,m4a,ogg,webm', 'max:10240']]);

        $path = $request->file('audio')->store('beep-calls/audio', 'public');

        return response()->json(['path' => $path]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensurePremium();

        $organization = auth()->user()->organization;

        $validated = $request->validate([
            'event_id' => ['required', 'exists:events,id'],
            'attendee_ids' => ['required', 'array'],
            'attendee_ids.*' => ['exists:attendees,id'],
            'audio_file' => ['nullable', 'file', 'mimes:mp3,wav,m4a,ogg', 'max:10240'],
            'audio_path' => ['nullable', 'string', 'max:500'],
            'call_schedule' => ['required', 'date', 'after_or_equal:now'],
        ]);

        $event = Event::findOrFail($validated['event_id']);
        if ($event->organization_id !== $organization->id) {
            abort(403);
        }

        if ($request->hasFile('audio_file')) {
            $audioPath = $request->file('audio_file')->store('beep-calls/audio', 'public');
        } elseif (! empty($validated['audio_path'])) {
            $audioPath = $validated['audio_path'];
        } else {
            return redirect()->back()->withErrors(['audio_file' => 'Please upload or record audio.']);
        }

        foreach ($validated['attendee_ids'] as $attendeeId) {
            $attendee = Attendee::findOrFail($attendeeId);
            if ($attendee->event_id !== $event->id || ! ($attendee->phone || $attendee->email)) {
                continue;
            }

            $organization->beepCalls()->create([
                'event_id' => $event->id,
                'attendee_id' => $attendee->id,
                'audio_file' => $audioPath,
                'call_schedule' => $validated['call_schedule'],
                'call_status' => BeepCall::STATUS_PENDING,
            ]);
        }

        return redirect()->route('beep-calls.index', ['event_id' => $event->id])
            ->with('status', 'Voice reminders scheduled.');
    }

    public function destroy(BeepCall $beepCall): RedirectResponse
    {
        $this->ensurePremium();

        $organization = auth()->user()->organization;

        if ($beepCall->organization_id !== $organization->id) {
            abort(403);
        }

        if (in_array($beepCall->call_status, [BeepCall::STATUS_PENDING, BeepCall::STATUS_QUEUED])) {
            if ($beepCall->audio_file) {
                Storage::disk('public')->delete($beepCall->audio_file);
            }
            $beepCall->delete();
        }

        return redirect()->route('beep-calls.index')->with('status', 'Beep call cancelled.');
    }

    public function callNow(BeepCall $beepCall): RedirectResponse
    {
        $this->ensurePremium();

        $organization = auth()->user()->organization;

        if ($beepCall->organization_id !== $organization->id) {
            abort(403);
        }

        if (! in_array($beepCall->call_status, [BeepCall::STATUS_PENDING, BeepCall::STATUS_QUEUED])) {
            return redirect()->route('beep-calls.index')->with('error', 'Call already processed.');
        }

        $beepCall->update(['call_schedule' => now()]);
        PlaceBeepCallJob::dispatch($beepCall);

        return redirect()->route('beep-calls.index')->with('status', 'Call placed.');
    }
}

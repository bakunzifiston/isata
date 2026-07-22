<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBeepCallRequest;
use App\Http\Requests\UploadBeepCallAudioRequest;
use App\Jobs\PlaceBeepCallJob;
use App\Models\Attendee;
use App\Models\BeepCall;
use App\Models\Event;
use App\Models\SubscriptionPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BeepCallController extends Controller
{
    private function ensureBeepCallsAccess(): ?RedirectResponse
    {
        if (auth()->user()->can('viewAny', BeepCall::class)) {
            return null;
        }

        return redirect()
            ->route('subscription.upgrade', ['plan' => SubscriptionPlan::SLUG_PREMIUM])
            ->with('error', 'Beep calls are included on the Premium plan. Upgrade to schedule voice reminders to attendees.');
    }

    public function index(Request $request): View|RedirectResponse
    {
        if ($redirect = $this->ensureBeepCallsAccess()) {
            return $redirect;
        }

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

    public function create(Request $request): View|RedirectResponse
    {
        if ($redirect = $this->ensureBeepCallsAccess()) {
            return $redirect;
        }

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

    public function uploadAudio(UploadBeepCallAudioRequest $request): \Illuminate\Http\JsonResponse
    {
        $path = $request->file('audio')->store('beep-calls/audio', 'public');

        return response()->json(['path' => $path]);
    }

    public function store(StoreBeepCallRequest $request): RedirectResponse
    {
        $organization = auth()->user()->organization;
        $validated = $request->validated();

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
        if ($redirect = $this->ensureBeepCallsAccess()) {
            return $redirect;
        }

        $this->authorize('delete', $beepCall);

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
        if ($redirect = $this->ensureBeepCallsAccess()) {
            return $redirect;
        }

        $this->authorize('callNow', $beepCall);

        if (! in_array($beepCall->call_status, [BeepCall::STATUS_PENDING, BeepCall::STATUS_QUEUED])) {
            return redirect()->route('beep-calls.index')->with('error', 'Call already processed.');
        }

        $beepCall->update(['call_schedule' => now()]);
        PlaceBeepCallJob::dispatch($beepCall);

        return redirect()->route('beep-calls.index')->with('status', 'Call placed.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventReminderSettings;
use App\Models\MessageTemplate;
use App\Models\OrganizationUsage;
use App\Notifications\EventCreatedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(Request $request): View
    {
        $organization = auth()->user()->organization;

        if (! $organization) {
            abort(403, 'No organization associated with your account.');
        }

        if (! Schema::hasTable('events')) {
            $events = new LengthAwarePaginator(
                collect(),
                0,
                15,
                1,
                ['path' => $request->url(), 'query' => $request->query()]
            );
            return view('events.index', ['events' => $events]);
        }

        $query = $organization->events()->with('creator')->orderBy('date', 'desc')->orderBy('time', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $events = $query->paginate(15);

        return view('events.index', [
            'events' => $events,
        ]);
    }

    public function create(): View
    {
        $organization = auth()->user()->organization;

        if (! $organization) {
            abort(403, 'No organization associated with your account.');
        }

        return view('events.create', [
            'event' => new Event(['status' => Event::STATUS_DRAFT]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $organization = auth()->user()->organization;

        if (! $organization) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'date' => ['required', 'date'],
            'time' => ['nullable', 'string', 'regex:/^\d{2}:\d{2}$/'],
            'venue' => ['nullable', 'string', 'max:255'],
            'meeting_link' => ['nullable', 'url', 'max:500'],
            'status' => ['required', 'in:draft,scheduled'],
        ]);

        $validated['time'] = $validated['time'] ? $validated['time'] . ':00' : null;

        if (! Schema::hasTable('events')) {
            return redirect()->route('events.create')
                ->with('error', 'Events cannot be created at the moment. Please try again later or contact support.')
                ->withInput($request->except('_token'));
        }

        $event = $organization->events()->create([
            ...$validated,
            'created_by' => auth()->id(),
        ]);

        if ($validated['status'] === Event::STATUS_SCHEDULED) {
            $this->incrementEventsUsage($organization->id);
        }

        foreach ($organization->admins as $admin) {
            $admin->notify(new EventCreatedNotification($event));
        }

        // Offline-ready placeholder: Draft events can be queued for sync when back online.
        // Future: Store draft in localStorage/IndexedDB, sync via background sync API.
        $message = $validated['status'] === Event::STATUS_DRAFT
            ? 'Event saved as draft.'
            : 'Event created successfully.';

        // Redirect to index instead of show to avoid accidental 404 from stale/wrong event links
        // in environments with inconsistent data or tenant-scoped access mismatches.
        return redirect()->route('events.index')->with('status', $message);
    }

    public function show(Event $event): View
    {
        $organization = auth()->user()->organization;

        if (! $organization || $event->organization_id !== $organization->id) {
            redirect()->route('events.index')
                ->with('error', 'That event is not available in your organization.')
                ->send();
            exit;
        }

        $event->load('creator');

        return view('events.show', [
            'event' => $event,
        ]);
    }

    public function edit(Event $event): View
    {
        $organization = auth()->user()->organization;

        if (! $organization || $event->organization_id !== $organization->id) {
            redirect()->route('events.index')
                ->with('error', 'That event is not available in your organization.')
                ->send();
            exit;
        }

        $event->load('reminderSettings');
        $templates = $organization->messageTemplates()->with('channel')->orderBy('name')->get();

        return view('events.edit', [
            'event' => $event,
            'templates' => $templates,
        ]);
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        $organization = auth()->user()->organization;

        if (! $organization || $event->organization_id !== $organization->id) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'date' => ['required', 'date'],
            'time' => ['nullable', 'string', 'regex:/^\d{2}:\d{2}$/'],
            'venue' => ['nullable', 'string', 'max:255'],
            'meeting_link' => ['nullable', 'url', 'max:500'],
            'status' => ['required', 'in:draft,scheduled,cancelled,completed'],
        ]);

        $validated['time'] = $validated['time'] ? $validated['time'] . ':00' : null;

        $wasScheduled = $event->isScheduled();
        $event->update($validated);

        if (! $wasScheduled && $validated['status'] === Event::STATUS_SCHEDULED) {
            $this->incrementEventsUsage($organization->id);
        }

        $reminder24hr = $request->input('reminder_24hr_template_id');
        $reminder1hr = $request->input('reminder_1hr_template_id');
        $settings = $event->reminderSettings()->firstOrCreate([], ['event_id' => $event->id]);
        $settings->update([
            'reminder_24hr_template_id' => $reminder24hr ?: null,
            'reminder_1hr_template_id' => $reminder1hr ?: null,
        ]);

        return redirect()->route('events.show', $event)->with('status', 'Event updated successfully.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        $organization = auth()->user()->organization;

        if (! $organization || $event->organization_id !== $organization->id) {
            abort(403, 'Unauthorized.');
        }

        $event->delete();

        return redirect()->route('events.index')->with('status', 'Event deleted successfully.');
    }

    public function calendar(): View
    {
        $organization = auth()->user()->organization;

        if (! $organization) {
            abort(403, 'No organization associated with your account.');
        }

        return view('events.calendar', [
            'organization' => $organization,
        ]);
    }

    public function calendarData(Request $request)
    {
        $organization = auth()->user()->organization;

        if (! $organization) {
            return response()->json([]);
        }

        $start = $request->input('start', now()->startOfMonth()->format('Y-m-d'));
        $end = $request->input('end', now()->endOfMonth()->format('Y-m-d'));

        $events = $organization->events()
            ->whereBetween('date', [$start, $end])
            ->whereIn('status', [Event::STATUS_SCHEDULED, Event::STATUS_COMPLETED])
            ->get()
            ->map(function (Event $e) {
                $startStr = $e->date->format('Y-m-d');
                if ($e->time) {
                    $startStr .= 'T' . substr($e->time, 0, 5);
                }
                return [
                    'id' => $e->id,
                    'title' => $e->name,
                    'start' => $startStr,
                    'url' => route('events.show', $e),
                    'extendedProps' => [
                        'status' => $e->status,
                        'venue' => $e->venue,
                    ],
                ];
            });

        return response()->json($events);
    }

    private function incrementEventsUsage(int $organizationId): void
    {
        $period = now()->format('Y-m');
        $usage = OrganizationUsage::getOrCreateForPeriod($organizationId, $period);
        $usage->increment('events_count');
    }
}

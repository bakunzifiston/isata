<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\StoreEventWizardRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Models\Channel;
use App\Models\Event;
use App\Models\Message;
use App\Models\OrganizationUsage;
use App\Notifications\EventCreatedNotification;
use App\Services\ContactService;
use App\Services\UsageLimitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class EventController extends Controller
{
    public function __construct(
        protected ContactService $contactService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Event::class);

        $organization = auth()->user()->organization;

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
        $this->authorize('create', Event::class);

        $channels = Channel::orderBy('name')->get();

        return view('events.wizard', [
            'event' => new Event([
                'status' => Event::STATUS_DRAFT,
                'event_format' => Event::FORMAT_PHYSICAL,
            ]),
            'channels' => $channels,
        ]);
    }

    public function storeWizard(StoreEventWizardRequest $request): RedirectResponse
    {
        $organization = auth()->user()->organization;
        $validated = $request->normalizedEvent();

        if (! Schema::hasTable('events')) {
            return redirect()->route('events.create')
                ->with('error', 'Events cannot be created at the moment. Please try again later or contact support.')
                ->withInput($request->except('_token'));
        }

        if ($validated['status'] === Event::STATUS_SCHEDULED) {
            app(UsageLimitService::class)->assertCanScheduleEvent($organization);
        }

        $event = DB::transaction(function () use ($organization, $validated, $request) {
            $event = $organization->events()->create([
                ...$validated,
                'created_by' => auth()->id(),
            ]);

            if (filled($request->input('guests_bulk'))) {
                $lines = array_filter(array_map('trim', explode("\n", $request->input('guests_bulk'))));
                $rows = [];
                foreach ($lines as $line) {
                    $parts = array_map('trim', str_getcsv($line));
                    if (count($parts) < 2) {
                        continue;
                    }
                    $rows[] = [
                        'name' => $parts[0],
                        'email' => $parts[1],
                        'phone' => $parts[2] ?? null,
                        'company' => $parts[3] ?? null,
                    ];
                }
                if (! empty($rows)) {
                    $this->contactService->bulkAssignToEvent($event, $rows);
                }
            }

            if ($request->shouldCreateMessage()) {
                $event->messages()->create([
                    'channel_id' => $request->input('message_channel_id'),
                    'content' => $request->input('message_content'),
                    'content_type' => Message::CONTENT_TYPE_TEXT,
                    'status' => $request->input('message_status', Message::STATUS_DRAFT),
                ]);
            }

            return $event;
        });

        if ($validated['status'] === Event::STATUS_SCHEDULED) {
            $this->incrementEventsUsage($organization->id);
        }

        foreach ($organization->admins as $admin) {
            $admin->notify(new EventCreatedNotification($event));
        }

        $message = $validated['status'] === Event::STATUS_DRAFT
            ? 'Event saved as draft.'
            : 'Event created successfully.';

        return redirect()->route('events.show', $event)->with('status', $message);
    }

    public function store(StoreEventRequest $request): RedirectResponse
    {
        $organization = auth()->user()->organization;
        $validated = $request->normalized();

        if (! Schema::hasTable('events')) {
            return redirect()->route('events.create')
                ->with('error', 'Events cannot be created at the moment. Please try again later or contact support.')
                ->withInput($request->except('_token'));
        }

        if ($validated['status'] === Event::STATUS_SCHEDULED) {
            app(UsageLimitService::class)->assertCanScheduleEvent($organization);
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

        $message = $validated['status'] === Event::STATUS_DRAFT
            ? 'Event saved as draft.'
            : 'Event created successfully.';

        return redirect()->route('events.index')->with('status', $message);
    }

    public function show(Event $event): View|RedirectResponse
    {
        if (! $this->userCanAccessEvent($event)) {
            return redirect()->route('events.index')
                ->with('error', 'That event is not available in your organization.');
        }

        $event->load('creator');

        return view('events.show', [
            'event' => $event,
        ]);
    }

    public function edit(Event $event): View|RedirectResponse
    {
        if (! $this->userCanAccessEvent($event)) {
            return redirect()->route('events.index')
                ->with('error', 'That event is not available in your organization.');
        }

        $organization = auth()->user()->organization;
        $event->load('reminderSettings');
        $templates = $organization->messageTemplates()->with('channel')->orderBy('name')->get();

        return view('events.edit', [
            'event' => $event,
            'templates' => $templates,
        ]);
    }

    public function update(UpdateEventRequest $request, Event $event): RedirectResponse
    {
        $organization = auth()->user()->organization;
        $validated = $request->normalized();

        $wasScheduled = $event->isScheduled();

        if (! $wasScheduled && $validated['status'] === Event::STATUS_SCHEDULED) {
            app(UsageLimitService::class)->assertCanScheduleEvent($organization);
        }

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
        $this->authorize('delete', $event);

        $event->delete();

        return redirect()->route('events.index')->with('status', 'Event deleted successfully.');
    }

    public function calendar(): View
    {
        $this->authorize('viewAny', Event::class);

        return view('events.calendar', [
            'organization' => auth()->user()->organization,
        ]);
    }

    public function calendarData(Request $request)
    {
        $this->authorize('viewAny', Event::class);

        $organization = auth()->user()->organization;

        $start = $request->input('start', now()->startOfMonth()->format('Y-m-d'));
        $end = $request->input('end', now()->endOfMonth()->format('Y-m-d'));

        $events = $organization->events()
            ->whereBetween('date', [$start, $end])
            ->whereIn('status', [Event::STATUS_SCHEDULED, Event::STATUS_COMPLETED])
            ->get()
            ->map(function (Event $e) {
                $startStr = $e->date->format('Y-m-d');
                if ($e->time) {
                    $startStr .= 'T'.substr($e->time, 0, 5);
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

    private function userCanAccessEvent(Event $event): bool
    {
        return auth()->user()->can('view', $event);
    }

    private function incrementEventsUsage(int $organizationId): void
    {
        $period = now()->format('Y-m');
        $usage = OrganizationUsage::getOrCreateForPeriod($organizationId, $period);
        $usage->increment('events_count');
    }
}

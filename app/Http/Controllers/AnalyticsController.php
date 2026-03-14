<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use App\Models\CommunicationLog;
use App\Models\Event;
use App\Models\Message;
use App\Models\Rsvp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function index(Request $request): View
    {
        $organization = auth()->user()->organization;

        if (! $organization) {
            abort(403);
        }

        if (! Schema::hasTable('events')) {
            return view('analytics.index', [
                'kpis' => [
                    'messages_sent' => 0,
                    'delivery_rate' => 0,
                    'open_rate' => 0,
                    'rsvp_rate' => 0,
                    'attendance_rate' => 0,
                    'social_engagement' => 0,
                ],
                'events' => collect(),
                'selectedEvent' => null,
                'chartData' => [
                    'bar' => ['labels' => [], 'values' => [], 'colors' => []],
                    'line' => ['labels' => [], 'values' => []],
                    'pie' => [
                        'labels' => ['Delivered', 'Opened', 'RSVP\'d', 'Attended'],
                        'values' => [0, 0, 0, 0],
                        'colors' => ['#10b981', '#3b82f6', '#8b5cf6', '#f59e0b'],
                    ],
                ],
                'eventPerformance' => collect(),
            ]);
        }

        $eventId = $request->input('event_id');
        $eventIds = $organization->events()->pluck('id');

        $messagesSent = Message::whereIn('event_id', $eventIds)->where('status', Message::STATUS_SENT)->count();

        $logs = CommunicationLog::where('organization_id', $organization->id);
        if ($eventId) {
            $logs->where('event_id', $eventId);
        }
        $logs = $logs->get();

        $totalSent = $logs->count();
        $delivered = $logs->whereIn('status', [CommunicationLog::STATUS_DELIVERED, CommunicationLog::STATUS_SENT])->count();
        $opened = $logs->whereNotNull('opened_at')->count();
        $deliveryRate = $totalSent > 0 ? round(($delivered / $totalSent) * 100, 1) : 0;
        $openRate = $delivered > 0 ? round(($opened / $delivered) * 100, 1) : 0;

        $events = $organization->events()
            ->whereIn('status', [Event::STATUS_SCHEDULED, Event::STATUS_COMPLETED])
            ->orderBy('date', 'desc')
            ->get();

        $selectedEvent = $eventId ? $events->firstWhere('id', (int) $eventId) : null;

        $rsvpRate = 0;
        $attendanceRate = 0;
        $socialEngagement = 0;

        $eventIdsForMetrics = $eventId ? [(int) $eventId] : $eventIds->toArray();
        $totalAttendees = Attendee::whereIn('event_id', $eventIdsForMetrics)->count();
        $respondedCount = Rsvp::whereIn('event_id', $eventIdsForMetrics)->distinct('attendee_id')->count('attendee_id');
        $attendedCount = Attendee::whereIn('event_id', $eventIdsForMetrics)->where('rsvp_status', Attendee::RSVP_ATTENDED)->count();
        $confirmedCount = Attendee::whereIn('event_id', $eventIdsForMetrics)->whereIn('rsvp_status', [Attendee::RSVP_CONFIRMED, Attendee::RSVP_ATTENDED])->count();

        if ($totalAttendees > 0) {
            $rsvpRate = round(($respondedCount / $totalAttendees) * 100, 1);
            $attendanceRate = $confirmedCount > 0 ? round(($attendedCount / $confirmedCount) * 100, 1) : 0;
        }

        $socialChannel = \App\Models\Channel::where('slug', 'social_media')->first();
        $socialSent = $socialChannel ? $logs->where('channel_id', $socialChannel->id)->count() : 0;
        $socialOpened = $socialChannel ? $logs->where('channel_id', $socialChannel->id)->whereNotNull('opened_at')->count() : 0;
        $socialEngagement = $socialSent > 0 ? round(($socialOpened / $socialSent) * 100, 1) : 0;

        $kpis = [
            'messages_sent' => $messagesSent,
            'delivery_rate' => $deliveryRate,
            'open_rate' => $openRate,
            'rsvp_rate' => $rsvpRate,
            'attendance_rate' => $attendanceRate,
            'social_engagement' => $socialEngagement,
        ];

        $logs = $logs->load('channel');
        $messagesByChannel = $logs->groupBy('channel_id')->map->count();
        $channels = \App\Models\Channel::whereIn('id', $messagesByChannel->keys())->get()->keyBy('id');

        $messagesByDay = $logs->groupBy(fn ($l) => $l->sent_at->format('Y-m-d'))->map->count();
        $last7Days = collect(range(0, 6))->map(fn ($i) => now()->subDays($i)->format('Y-m-d'))->reverse();
        $lineChartData = $last7Days->map(fn ($d) => $messagesByDay[$d] ?? 0)->values();

        $pieData = [
            'labels' => ['Delivered', 'Opened', 'RSVP\'d', 'Attended'],
            'values' => [
                $delivered,
                $opened,
                $respondedCount,
                Attendee::whereIn('event_id', $eventIdsForMetrics)->where('rsvp_status', Attendee::RSVP_ATTENDED)->count(),
            ],
            'colors' => ['#10b981', '#3b82f6', '#8b5cf6', '#f59e0b'],
        ];

        $eventPerformance = $events->take(10)->map(function (Event $e) {
            $attendees = $e->attendees()->count();
            $responded = $e->rsvps()->distinct('attendee_id')->count('attendee_id');
            $attended = $e->attendees()->where('rsvp_status', Attendee::RSVP_ATTENDED)->count();
            $messages = $e->messages()->where('status', Message::STATUS_SENT)->count();

            return [
                'event' => $e,
                'attendees' => $attendees,
                'responded' => $responded,
                'attended' => $attended,
                'messages' => $messages,
                'rsvp_rate' => $attendees > 0 ? round(($responded / $attendees) * 100, 1) : 0,
                'attendance_rate' => $responded > 0 ? round(($attended / $responded) * 100, 1) : 0,
            ];
        });

        return view('analytics.index', [
            'kpis' => $kpis,
            'events' => $events,
            'selectedEvent' => $selectedEvent,
            'chartData' => [
                'bar' => [
                    'labels' => $messagesByChannel->keys()->map(fn ($id) => $channels->get($id)?->name ?? 'Channel')->values()->toArray(),
                    'values' => $messagesByChannel->values()->toArray(),
                    'colors' => ['#6366f1', '#10b981', '#f59e0b', '#ec4899'],
                ],
                'line' => [
                    'labels' => $last7Days->map(fn ($d) => \Carbon\Carbon::parse($d)->format('M j'))->values()->toArray(),
                    'values' => $lineChartData->toArray(),
                ],
                'pie' => $pieData,
            ],
            'eventPerformance' => $eventPerformance,
        ]);
    }

    public function eventReport(Event $event): View
    {
        $organization = auth()->user()->organization;

        if (! $organization || $event->organization_id !== $organization->id) {
            abort(404);
        }

        $attendees = $event->attendees;
        $total = $attendees->count();
        $responded = $event->rsvps()->distinct('attendee_id')->count('attendee_id');
        $confirmed = $attendees->whereIn('rsvp_status', [Attendee::RSVP_CONFIRMED, Attendee::RSVP_ATTENDED])->count();
        $attended = $attendees->where('rsvp_status', Attendee::RSVP_ATTENDED)->count();
        $messages = $event->messages()->where('status', Message::STATUS_SENT)->count();

        $logs = CommunicationLog::where('event_id', $event->id)->get();
        $delivered = $logs->whereIn('status', [CommunicationLog::STATUS_DELIVERED, CommunicationLog::STATUS_SENT])->count();
        $opened = $logs->whereNotNull('opened_at')->count();

        return view('analytics.event-report', [
            'event' => $event,
            'metrics' => [
                'total_attendees' => $total,
                'responded' => $responded,
                'confirmed' => $confirmed,
                'attended' => $attended,
                'messages_sent' => $messages,
                'rsvp_rate' => $total > 0 ? round(($responded / $total) * 100, 1) : 0,
                'attendance_rate' => $responded > 0 ? round(($attended / $responded) * 100, 1) : 0,
                'delivery_rate' => $logs->count() > 0 ? round(($delivered / $logs->count()) * 100, 1) : 0,
                'open_rate' => $delivered > 0 ? round(($opened / $delivered) * 100, 1) : 0,
            ],
            'logs' => $logs->load(['attendee', 'channel'])->take(50),
        ]);
    }
}

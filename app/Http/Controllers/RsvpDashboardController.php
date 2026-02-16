<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RsvpDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $organization = auth()->user()->organization;

        if (! $organization) {
            abort(403);
        }

        $eventId = $request->input('event_id');
        $events = $organization->events()
            ->whereIn('status', [Event::STATUS_SCHEDULED, Event::STATUS_COMPLETED])
            ->orderBy('date', 'desc')
            ->get();

        $selectedEvent = $eventId
            ? $events->firstWhere('id', (int) $eventId)
            : $events->first();

        $stats = null;
        $chartData = null;

        if ($selectedEvent) {
            $attendees = $selectedEvent->attendees;
            $total = $attendees->count();

            $confirmed = $attendees->where('rsvp_status', Attendee::RSVP_CONFIRMED)->count();
            $pending = $attendees->where('rsvp_status', Attendee::RSVP_PENDING)->count();
            $declined = $attendees->where('rsvp_status', Attendee::RSVP_DECLINED)->count();
            $attended = $attendees->where('rsvp_status', Attendee::RSVP_ATTENDED)->count();

            $noShow = 0;
            if ($selectedEvent->date && $selectedEvent->date->isPast()) {
                $noShow = $attendees->where('rsvp_status', Attendee::RSVP_CONFIRMED)->count();
            }

            $stats = [
                'total' => $total,
                'confirmed' => $confirmed,
                'pending' => $pending,
                'declined' => $declined,
                'attended' => $attended,
                'no_show' => $noShow,
            ];

            $chartData = [
                'labels' => ['Attended', 'Pending', 'No-show', 'Declined'],
                'values' => [
                    $stats['attended'],
                    $stats['pending'],
                    $stats['no_show'],
                    $stats['declined'],
                ],
                'colors' => ['#10b981', '#f59e0b', '#ef4444', '#6b7280'],
            ];
        }

        return view('rsvp.dashboard', [
            'events' => $events,
            'selectedEvent' => $selectedEvent,
            'stats' => $stats,
            'chartData' => $chartData,
        ]);
    }
}

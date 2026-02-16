<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use App\Models\CommunicationLog;
use App\Models\Event;
use App\Models\Message;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = auth()->user();
        $organization = $user->organization;
        $isAdmin = $user->isAdmin();

        $eventCount = 0;
        $attendeeCount = 0;
        $messagesSent = 0;
        $upcomingEvents = collect();
        $chartData = ['labels' => [], 'values' => []];
        $recentActivity = [];

        if ($organization) {
            $eventIds = $organization->events()->pluck('id');
            $eventCount = $organization->events()->whereIn('status', [Event::STATUS_SCHEDULED, Event::STATUS_COMPLETED])->count();
            $attendeeCount = Attendee::whereIn('event_id', $eventIds)->count();
            $messagesSent = Message::whereIn('event_id', $eventIds)->where('status', Message::STATUS_SENT)->count();

            $upcomingEvents = $organization->events()
                ->where('status', Event::STATUS_SCHEDULED)
                ->where('date', '>=', now()->toDateString())
                ->orderBy('date')
                ->orderBy('time')
                ->take(5)
                ->get();

            $logs = CommunicationLog::where('organization_id', $organization->id)->whereNotNull('sent_at')->get();
            $messagesByDay = $logs->groupBy(fn ($l) => $l->sent_at->format('Y-m-d'))->map->count();
            $last7Days = collect(range(6, 0))->map(fn ($i) => now()->subDays($i)->format('Y-m-d'));
            $chartData = [
                'labels' => $last7Days->map(fn ($d) => \Carbon\Carbon::parse($d)->format('M j'))->values()->toArray(),
                'values' => $last7Days->map(fn ($d) => $messagesByDay[$d] ?? 0)->values()->toArray(),
            ];
        }

        return view('dashboard', [
            'user' => $user,
            'organization' => $organization,
            'isAdmin' => $isAdmin,
            'eventCount' => $eventCount,
            'attendeeCount' => $attendeeCount,
            'messagesSent' => $messagesSent,
            'upcomingEvents' => $upcomingEvents,
            'chartData' => $chartData,
        ]);
    }
}

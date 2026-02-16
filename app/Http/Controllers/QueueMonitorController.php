<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class QueueMonitorController extends Controller
{
    public function index(): View
    {
        $organization = auth()->user()->organization;

        if (! $organization) {
            abort(403);
        }

        $eventIds = $organization->events()->pluck('id');

        $pending = Message::query()
            ->whereIn('event_id', $eventIds)
            ->where('status', Message::STATUS_SCHEDULED)
            ->where(function ($q) {
                $q->whereNull('scheduled_at')
                    ->orWhere('scheduled_at', '>', now());
            })
            ->with(['event', 'channel'])
            ->orderBy('scheduled_at')
            ->get();

        $due = Message::query()
            ->whereIn('event_id', $eventIds)
            ->where('status', Message::STATUS_SCHEDULED)
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->with(['event', 'channel'])
            ->get();

        $jobsCount = 0;
        if (config('queue.default') === 'database') {
            $jobsCount = DB::table('jobs')->count();
        }

        $failedCount = DB::table('failed_jobs')->count();

        return view('queue.monitor', [
            'pending' => $pending,
            'due' => $due,
            'jobsCount' => $jobsCount,
            'failedCount' => $failedCount,
        ]);
    }
}

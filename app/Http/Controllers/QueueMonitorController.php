<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class QueueMonitorController extends Controller
{
    public function index(): View
    {
        $organization = auth()->user()->organization;

        if (! $organization) {
            abort(403);
        }

        if (! Schema::hasTable('events')) {
            return view('queue.monitor', [
                'pending' => collect(),
                'due' => collect(),
                'jobsCount' => (config('queue.default') === 'database' && Schema::hasTable('jobs')) ? (int) DB::table('jobs')->count() : 0,
                'failedCount' => Schema::hasTable('failed_jobs') ? (int) DB::table('failed_jobs')->count() : 0,
            ]);
        }

        $eventIds = $organization->events()->pluck('id');

        $pending = Schema::hasTable('messages')
            ? Message::query()
                ->whereIn('event_id', $eventIds)
                ->where('status', Message::STATUS_SCHEDULED)
                ->where(function ($q) {
                    $q->whereNull('scheduled_at')
                        ->orWhere('scheduled_at', '>', now());
                })
                ->with(['event', 'channel'])
                ->orderBy('scheduled_at')
                ->get()
            : collect();

        $due = Schema::hasTable('messages')
            ? Message::query()
                ->whereIn('event_id', $eventIds)
                ->where('status', Message::STATUS_SCHEDULED)
                ->whereNotNull('scheduled_at')
                ->where('scheduled_at', '<=', now())
                ->with(['event', 'channel'])
                ->get()
            : collect();

        $jobsCount = 0;
        if (config('queue.default') === 'database' && Schema::hasTable('jobs')) {
            $jobsCount = DB::table('jobs')->count();
        }

        $failedCount = Schema::hasTable('failed_jobs') ? DB::table('failed_jobs')->count() : 0;

        return view('queue.monitor', [
            'pending' => $pending,
            'due' => $due,
            'jobsCount' => $jobsCount,
            'failedCount' => $failedCount,
        ]);
    }
}

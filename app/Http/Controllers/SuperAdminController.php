<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Attendee;
use App\Models\Channel;
use App\Models\CommunicationLog;
use App\Models\Event;
use App\Models\Message;
use App\Models\Organization;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SuperAdminController extends Controller
{
    public function dashboard(): View
    {
        $organizations = Organization::count();
        $activeOrgs = Schema::hasColumn('organizations', 'is_active')
            ? Organization::where('is_active', true)->count()
            : 0;
        $totalEvents = Schema::hasTable('events') ? Event::count() : 0;
        $totalAttendees = Schema::hasTable('attendees') ? Attendee::count() : 0;
        $messagesSent = Schema::hasTable('messages')
            ? Message::where('status', Message::STATUS_SENT)->count()
            : 0;
        $commLogs = Schema::hasTable('communication_logs')
            ? CommunicationLog::whereNotNull('sent_at')->count()
            : 0;

        if (Schema::hasTable('subscription_plans')) {
            $plans = SubscriptionPlan::withCount('organizations')->get();
            $mrr = $plans->sum(fn ($p) => ($p->organizations_count ?? 0) * (float) $p->price);
            $activeSubscriptions = Schema::hasColumn('organizations', 'subscription_plan_id')
                ? Organization::whereNotNull('subscription_plan_id')->count()
                : 0;
        } else {
            $plans = collect();
            $mrr = 0;
            $activeSubscriptions = 0;
        }

        $lastMonth = now()->subMonth();
        if (Schema::hasTable('events')) {
            $eventsThisMonth = Event::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();
            $eventsLastMonth = Event::whereMonth('created_at', $lastMonth->month)
                ->whereYear('created_at', $lastMonth->year)
                ->count();
            $eventsGrowth = $eventsLastMonth > 0
                ? round((($eventsThisMonth - $eventsLastMonth) / $eventsLastMonth) * 100, 1)
                : 0;
        } else {
            $eventsThisMonth = 0;
            $eventsLastMonth = 0;
            $eventsGrowth = 0;
        }

        $orgsThisMonth = Organization::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $orgsLastMonth = Organization::whereMonth('created_at', $lastMonth->month)->whereYear('created_at', $lastMonth->year)->count();
        $orgsGrowth = $orgsLastMonth > 0 ? round((($orgsThisMonth - $orgsLastMonth) / $orgsLastMonth) * 100, 1) : 0;

        if (Schema::hasTable('channels') && Schema::hasTable('communication_logs')) {
            $channels = Channel::all()->keyBy('id');
            $emailChannel = Channel::where('slug', Channel::SLUG_EMAIL)->first();
            $smsChannel = Channel::where('slug', Channel::SLUG_SMS)->first();
            $beepChannel = Channel::where('slug', Channel::SLUG_BEEP_CALL)->first();
            $socialChannel = Channel::where('slug', Channel::SLUG_SOCIAL_MEDIA)->first();

            $emailVolume = $emailChannel ? CommunicationLog::where('channel_id', $emailChannel->id)->count() : 0;
            $smsVolume = $smsChannel ? CommunicationLog::where('channel_id', $smsChannel->id)->count() : 0;
            $beepVolume = $beepChannel ? CommunicationLog::where('channel_id', $beepChannel->id)->count() : 0;
            $socialVolume = $socialChannel ? CommunicationLog::where('channel_id', $socialChannel->id)->count() : 0;

            $delivered = CommunicationLog::whereIn('status', [CommunicationLog::STATUS_DELIVERED, CommunicationLog::STATUS_SENT])->count();
            $totalComms = CommunicationLog::count();
            $deliveryRate = $totalComms > 0 ? round(($delivered / $totalComms) * 100, 1) : 0;
        } else {
            $channels = collect();
            $emailVolume = 0;
            $smsVolume = 0;
            $beepVolume = 0;
            $socialVolume = 0;
            $deliveryRate = 0;
        }

        $jobsCount = config('queue.default') === 'database' && Schema::hasTable('jobs')
            ? DB::table('jobs')->count()
            : 0;
        $failedCount = Schema::hasTable('failed_jobs') ? DB::table('failed_jobs')->count() : 0;

        $planDistribution = $plans->map(fn ($p) => [
            'name' => $p->name,
            'count' => $p->organizations_count ?? 0,
            'revenue' => ($p->organizations_count ?? 0) * (float) $p->price,
        ])->toArray();

        $revenueChartData = collect(range(5, 0))->map(function ($i) use ($plans) {
            $date = now()->subMonths($i);
            $endOfMonth = $date->copy()->endOfMonth();
            $revenue = 0;
            foreach ($plans as $plan) {
                $count = Organization::where('subscription_plan_id', $plan->id)
                    ->where('created_at', '<=', $endOfMonth)
                    ->count();
                $revenue += $count * (float) $plan->price;
            }
            return ['month' => $date->format('M Y'), 'revenue' => $revenue];
        });

        if (Schema::hasTable('events')) {
            $topEvents = Event::withCount('attendees')
                ->with('organization')
                ->orderByDesc('attendees_count')
                ->take(10)
                ->get()
                ->map(function (Event $e) {
                    $responded = $e->rsvps()->distinct('attendee_id')->count('attendee_id');
                    $attended = $e->attendees()->where('rsvp_status', Attendee::RSVP_ATTENDED)->count();
                    return [
                        'event' => $e,
                        'attendees' => $e->attendees_count,
                        'rsvp_rate' => $e->attendees_count > 0 ? round(($responded / $e->attendees_count) * 100, 1) : 0,
                        'attendance_rate' => $responded > 0 ? round(($attended / $responded) * 100, 1) : 0,
                    ];
                });
        } else {
            $topEvents = collect();
        }

        $recentActivity = Schema::hasTable('activity_logs')
            ? ActivityLog::with('user')->latest()->take(15)->get()
            : collect();

        $orgQuery = Organization::with('subscriptionPlan');
        if (Schema::hasTable('events')) {
            $orgQuery->withCount('events');
        }
        if (Schema::hasTable('users')) {
            $orgQuery->withCount('users');
        }
        $organizationsList = $orgQuery
            ->orderByDesc('created_at')
            ->paginate(10, ['*'], 'org_page');

        return view('super-admin.dashboard', [
            'organizations' => $organizations,
            'activeOrgs' => $activeOrgs,
            'totalEvents' => $totalEvents,
            'totalAttendees' => $totalAttendees,
            'messagesSent' => $messagesSent,
            'commLogs' => $commLogs,
            'mrr' => $mrr,
            'activeSubscriptions' => $activeSubscriptions,
            'eventsGrowth' => $eventsGrowth,
            'orgsGrowth' => $orgsGrowth,
            'emailVolume' => $emailVolume,
            'smsVolume' => $smsVolume,
            'beepVolume' => $beepVolume,
            'socialVolume' => $socialVolume,
            'deliveryRate' => $deliveryRate,
            'jobsCount' => $jobsCount,
            'failedCount' => $failedCount,
            'planDistribution' => $planDistribution,
            'revenueChartData' => $revenueChartData,
            'topEvents' => $topEvents,
            'recentActivity' => $recentActivity,
            'organizationsList' => $organizationsList,
        ]);
    }

    public function organizations(Request $request): View
    {
        $query = Organization::with('subscriptionPlan');

        if (Schema::hasTable('events')) {
            $query->withCount('events');
        }
        if (Schema::hasTable('users')) {
            $query->withCount('users');
        }

        if ($request->filled('status') && Schema::hasColumn('organizations', 'is_active')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $organizations = $query->orderByDesc('created_at')->paginate(20);

        $plans = Schema::hasTable('subscription_plans')
            ? SubscriptionPlan::orderBy('price')->get()
            : collect();

        return view('super-admin.organizations', [
            'organizations' => $organizations,
            'plans' => $plans,
        ]);
    }

    public function toggleOrganization(Organization $organization): RedirectResponse
    {
        $organization->update(['is_active' => ! $organization->is_active]);
        ActivityLog::log('organization_toggle', "Organization {$organization->name} " . ($organization->is_active ? 'activated' : 'deactivated'), ['organization_id' => $organization->id]);

        return redirect()->route('super-admin.organizations')->with('status', "Organization {$organization->name} " . ($organization->is_active ? 'activated' : 'deactivated') . '.');
    }

    public function assignPlan(Request $request, Organization $organization): RedirectResponse
    {
        $validated = $request->validate(['subscription_plan_id' => ['required', 'exists:subscription_plans,id']]);
        $organization->update($validated);
        ActivityLog::log('plan_assigned', "Plan assigned to {$organization->name}", ['organization_id' => $organization->id, 'plan_id' => $validated['subscription_plan_id']]);

        return redirect()->route('super-admin.organizations')->with('status', "Plan assigned to {$organization->name}.");
    }

    public function events(): View
    {
        if (Schema::hasTable('events')) {
            $events = Event::with('organization')
                ->withCount('attendees')
                ->orderByDesc('date')
                ->paginate(20);
        } else {
            $events = collect();
        }

        return view('super-admin.events', ['events' => $events]);
    }

    public function activity(): View
    {
        $logs = ActivityLog::with('user')->latest()->paginate(50);

        return view('super-admin.activity', ['logs' => $logs]);
    }

    public function systemHealth(): View
    {
        $jobsCount = config('queue.default') === 'database' ? DB::table('jobs')->count() : 0;
        $failedCount = DB::table('failed_jobs')->count();
        $failedJobs = DB::table('failed_jobs')->orderByDesc('failed_at')->take(20)->get();

        return view('super-admin.system-health', [
            'jobsCount' => $jobsCount,
            'failedCount' => $failedCount,
            'failedJobs' => $failedJobs,
        ]);
    }

    public function users(): View
    {
        $users = User::with('organization')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('super-admin.users', [
            'users' => $users,
        ]);
    }

    public function destroyUser(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()
                ->route('super-admin.users')
                ->with('error', 'You cannot delete your own super admin account.');
        }

        $name = $user->name;
        $email = $user->email;

        $user->delete();

        ActivityLog::log('user_deleted', "User {$name} ({$email}) deleted by super admin.", [
            'deleted_user_email' => $email,
        ]);

        return redirect()
            ->route('super-admin.users')
            ->with('status', 'User deleted successfully.');
    }
}

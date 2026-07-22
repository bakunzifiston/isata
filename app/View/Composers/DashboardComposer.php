<?php

namespace App\View\Composers;

use App\Models\OrganizationUsage;
use App\Services\UsageLimitService;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardComposer
{
    public function compose(View $view): void
    {
        $usageMeter = null;
        $usageWarnings = [];
        $notifications = [];
        $unreadNotificationsCount = 0;

        if (auth()->check() && auth()->user()->organization && Schema::hasTable('organization_usage')) {
            $org = auth()->user()->organization;
            $plan = $org->subscriptionPlan;
            $currentPeriod = now()->format('Y-m');
            $usage = OrganizationUsage::getOrCreateForPeriod($org->id, $currentPeriod);
            $limitService = app(UsageLimitService::class);

            $eventsLimit = $plan?->getEventsLimitAttribute();
            $contactsLimit = $plan?->getContactsLimitAttribute();
            $eventsPct = $limitService->eventsUsagePercent($org) ?? 0;
            $contactsPct = $limitService->contactsUsagePercent($org) ?? 0;

            $usageMeter = [
                'events_used' => $usage->events_count,
                'events_limit' => $eventsLimit ?? '∞',
                'events_pct' => $eventsPct,
                'events_near_limit' => $eventsLimit !== null && $eventsPct >= UsageLimitService::WARNING_THRESHOLD_PERCENT,
                'contacts_used' => $usage->contacts_count,
                'contacts_limit' => $contactsLimit ? number_format($contactsLimit) : '∞',
                'contacts_pct' => $contactsPct,
                'contacts_near_limit' => $contactsLimit !== null && $contactsPct >= UsageLimitService::WARNING_THRESHOLD_PERCENT,
            ];

            $usageWarnings = $limitService->usageWarnings($org);
        }

        $user = auth()->user();
        if ($user && Schema::hasTable('notifications')) {
            $dbNotifications = $user->unreadNotifications()->take(10)->get();
            $unreadNotificationsCount = $user->unreadNotifications()->count();
            $notifications = $dbNotifications->map(function ($n) {
                $data = $n->data;

                return [
                    'id' => $n->id,
                    'title' => $data['title'] ?? 'Notification',
                    'body' => $data['message'] ?? $data['body'] ?? '',
                    'url' => $data['url'] ?? '#',
                    'read_at' => $n->read_at,
                    'created_at' => $n->created_at->diffForHumans(),
                ];
            })->toArray();
        }

        $view->with(compact('usageMeter', 'usageWarnings', 'notifications', 'unreadNotificationsCount'));
    }
}

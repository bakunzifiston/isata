<?php

namespace App\View\Composers;

use App\Models\OrganizationUsage;
use Illuminate\View\View;

class DashboardComposer
{
    public function compose(View $view): void
    {
        $usageMeter = null;
        $notifications = [];
        $unreadNotificationsCount = 0;

        if (auth()->check() && auth()->user()->organization) {
            $org = auth()->user()->organization;
            $plan = $org->subscriptionPlan;
            $currentPeriod = now()->format('Y-m');
            $usage = OrganizationUsage::getOrCreateForPeriod($org->id, $currentPeriod);

            $eventsLimit = $plan?->getEventsLimitAttribute();
            $contactsLimit = $plan?->getContactsLimitAttribute();

            $usageMeter = [
                'events_used' => $usage->events_count,
                'events_limit' => $eventsLimit ?? '∞',
                'events_pct' => $eventsLimit ? min(100, ($usage->events_count / $eventsLimit) * 100) : 0,
                'contacts_used' => $usage->contacts_count,
                'contacts_limit' => $contactsLimit ? number_format($contactsLimit) : '∞',
                'contacts_pct' => $contactsLimit ? min(100, ($usage->contacts_count / $contactsLimit) * 100) : 0,
            ];
        }

        $user = auth()->user();
        if ($user) {
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

        $view->with(compact('usageMeter', 'notifications', 'unreadNotificationsCount'));
    }
}

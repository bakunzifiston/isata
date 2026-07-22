<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\OrganizationUsage;
use Illuminate\Validation\ValidationException;

class UsageLimitService
{
    public const WARNING_THRESHOLD_PERCENT = 80;

    public function currentUsage(Organization $organization): OrganizationUsage
    {
        return OrganizationUsage::getOrCreateForPeriod(
            $organization->id,
            now()->format('Y-m')
        );
    }

    public function eventsLimit(Organization $organization): ?int
    {
        return $organization->subscriptionPlan?->getEventsLimitAttribute();
    }

    public function contactsLimit(Organization $organization): ?int
    {
        return $organization->subscriptionPlan?->getContactsLimitAttribute();
    }

    public function eventsUsagePercent(Organization $organization): ?float
    {
        $limit = $this->eventsLimit($organization);
        if ($limit === null) {
            return null;
        }

        return min(100, ($this->currentUsage($organization)->events_count / $limit) * 100);
    }

    public function contactsUsagePercent(Organization $organization): ?float
    {
        $limit = $this->contactsLimit($organization);
        if ($limit === null) {
            return null;
        }

        return min(100, ($this->currentUsage($organization)->contacts_count / $limit) * 100);
    }

    /**
     * @return list<string>
     */
    public function usageWarnings(Organization $organization): array
    {
        $warnings = [];
        $usage = $this->currentUsage($organization);
        $upgradeUrl = route('subscription.upgrade');

        $eventsLimit = $this->eventsLimit($organization);
        if ($eventsLimit !== null) {
            $eventsPct = ($usage->events_count / $eventsLimit) * 100;
            if ($eventsPct >= self::WARNING_THRESHOLD_PERCENT && $eventsPct < 100) {
                $warnings[] = "You're nearing your monthly event limit ({$usage->events_count}/{$eventsLimit}). Consider upgrading: {$upgradeUrl}";
            }
        }

        $contactsLimit = $this->contactsLimit($organization);
        if ($contactsLimit !== null) {
            $contactsPct = ($usage->contacts_count / $contactsLimit) * 100;
            if ($contactsPct >= self::WARNING_THRESHOLD_PERCENT && $contactsPct < 100) {
                $warnings[] = "You're nearing your monthly contact limit ({$usage->contacts_count}/{$contactsLimit}). Consider upgrading: {$upgradeUrl}";
            }
        }

        return $warnings;
    }

    public function assertCanScheduleEvent(Organization $organization): void
    {
        $limit = $this->eventsLimit($organization);
        if ($limit === null) {
            return;
        }

        $used = $this->currentUsage($organization)->events_count;
        if ($used >= $limit) {
            throw ValidationException::withMessages([
                'status' => [
                    "You've reached your monthly event limit ({$limit}). Upgrade your plan to schedule more events: ".route('subscription.upgrade'),
                ],
            ]);
        }
    }

    public function assertCanAddContacts(Organization $organization, int $count = 1, string $field = 'email'): void
    {
        $limit = $this->contactsLimit($organization);
        if ($limit === null || $count === 0) {
            return;
        }

        $used = $this->currentUsage($organization)->contacts_count;
        if ($used + $count > $limit) {
            $remaining = max(0, $limit - $used);
            throw ValidationException::withMessages([
                $field => [
                    $count === 1
                        ? "You've reached your monthly contact limit ({$limit}). Upgrade your plan to add more contacts: ".route('subscription.upgrade')
                        : "Adding {$count} contacts would exceed your monthly limit ({$limit}; {$remaining} remaining). Upgrade your plan: ".route('subscription.upgrade'),
                ],
            ]);
        }
    }
}

<?php

namespace App\Policies\Concerns;

use App\Models\Event;
use App\Models\User;

trait BelongsToOrganization
{
    protected function hasOrganization(User $user): bool
    {
        return $user->organization_id !== null;
    }

    protected function isOrgAdmin(User $user): bool
    {
        return $user->isOrganizationAdmin();
    }

    protected function ownsEvent(User $user, ?Event $event): bool
    {
        return $event !== null
            && $this->hasOrganization($user)
            && $user->organization_id === $event->organization_id;
    }

    protected function ownsOrganizationId(User $user, ?int $organizationId): bool
    {
        return $this->hasOrganization($user)
            && $organizationId !== null
            && $user->organization_id === $organizationId;
    }

    protected function hasPremiumPlan(User $user): bool
    {
        return $this->hasOrganization($user)
            && (bool) $user->organization?->subscriptionPlan?->hasBeepCalls();
    }
}

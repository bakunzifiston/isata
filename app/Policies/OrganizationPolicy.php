<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;
use App\Policies\Concerns\BelongsToOrganization;

class OrganizationPolicy
{
    use BelongsToOrganization;

    public function view(User $user, Organization $organization): bool
    {
        return $this->ownsOrganizationId($user, $organization->id);
    }

    public function update(User $user, Organization $organization): bool
    {
        return $this->isOrgAdmin($user)
            && $this->ownsOrganizationId($user, $organization->id);
    }

    public function manageBilling(User $user, Organization $organization): bool
    {
        return $this->update($user, $organization);
    }
}

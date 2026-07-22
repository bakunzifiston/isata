<?php

namespace App\Policies;

use App\Models\User;
use App\Policies\Concerns\BelongsToOrganization;

class UserPolicy
{
    use BelongsToOrganization;

    public function viewAny(User $user): bool
    {
        return $this->hasOrganization($user);
    }

    public function create(User $user): bool
    {
        return $this->isOrgAdmin($user);
    }

    public function update(User $user, User $model): bool
    {
        return $this->isOrgAdmin($user)
            && $this->ownsOrganizationId($user, $model->organization_id);
    }

    public function delete(User $user, User $model): bool
    {
        return $this->update($user, $model);
    }
}

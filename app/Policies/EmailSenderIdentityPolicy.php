<?php

namespace App\Policies;

use App\Models\EmailSenderIdentity;
use App\Models\User;
use App\Policies\Concerns\BelongsToOrganization;

class EmailSenderIdentityPolicy
{
    use BelongsToOrganization;

    public function viewAny(User $user): bool
    {
        return $this->hasOrganization($user);
    }

    public function create(User $user): bool
    {
        return $this->hasOrganization($user);
    }

    public function update(User $user, EmailSenderIdentity $identity): bool
    {
        return $this->ownsOrganizationId($user, $identity->organization_id);
    }

    public function delete(User $user, EmailSenderIdentity $identity): bool
    {
        return $this->update($user, $identity);
    }
}

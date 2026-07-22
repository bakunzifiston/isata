<?php

namespace App\Policies;

use App\Models\MessageTemplate;
use App\Models\User;
use App\Policies\Concerns\BelongsToOrganization;

class MessageTemplatePolicy
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

    public function update(User $user, MessageTemplate $template): bool
    {
        return $this->ownsOrganizationId($user, $template->organization_id);
    }

    public function delete(User $user, MessageTemplate $template): bool
    {
        return $this->update($user, $template);
    }
}

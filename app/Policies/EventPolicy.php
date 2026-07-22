<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;
use App\Policies\Concerns\BelongsToOrganization;

class EventPolicy
{
    use BelongsToOrganization;

    public function viewAny(User $user): bool
    {
        return $this->hasOrganization($user);
    }

    public function view(User $user, Event $event): bool
    {
        return $this->ownsEvent($user, $event);
    }

    public function create(User $user): bool
    {
        return $this->hasOrganization($user);
    }

    public function update(User $user, Event $event): bool
    {
        return $this->ownsEvent($user, $event);
    }

    public function delete(User $user, Event $event): bool
    {
        return $this->ownsEvent($user, $event);
    }
}

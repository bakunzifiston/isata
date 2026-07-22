<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\Message;
use App\Models\User;
use App\Policies\Concerns\BelongsToOrganization;

class MessagePolicy
{
    use BelongsToOrganization;

    public function viewAny(User $user): bool
    {
        return $this->hasOrganization($user);
    }

    public function viewAnyForEvent(User $user, Event $event): bool
    {
        return $this->ownsEvent($user, $event);
    }

    public function create(User $user): bool
    {
        return $this->hasOrganization($user);
    }

    public function createForEvent(User $user, Event $event): bool
    {
        return $this->ownsEvent($user, $event);
    }

    public function update(User $user, Message $message): bool
    {
        $message->loadMissing(['event', 'channel']);

        if ($message->isSocialMessage()) {
            return $this->ownsOrganizationId($user, $message->organization_id);
        }

        return $this->ownsEvent($user, $message->event);
    }

    public function delete(User $user, Message $message): bool
    {
        return $this->update($user, $message);
    }

    public function sendNow(User $user, Message $message): bool
    {
        return $this->update($user, $message);
    }
}

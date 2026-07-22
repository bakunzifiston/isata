<?php

namespace App\Policies;

use App\Models\Attendee;
use App\Models\Event;
use App\Models\User;
use App\Policies\Concerns\BelongsToOrganization;

class AttendeePolicy
{
    use BelongsToOrganization;

    public function viewAny(User $user, Event $event): bool
    {
        return $this->ownsEvent($user, $event);
    }

    public function create(User $user, Event $event): bool
    {
        return $this->ownsEvent($user, $event);
    }

    public function update(User $user, Attendee $attendee): bool
    {
        $attendee->loadMissing('event');

        return $this->ownsEvent($user, $attendee->event);
    }

    public function delete(User $user, Attendee $attendee): bool
    {
        return $this->update($user, $attendee);
    }
}

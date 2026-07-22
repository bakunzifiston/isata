<?php

namespace App\Policies;

use App\Models\BeepCall;
use App\Models\User;
use App\Policies\Concerns\BelongsToOrganization;

class BeepCallPolicy
{
    use BelongsToOrganization;

    public function viewAny(User $user): bool
    {
        return $this->hasPremiumPlan($user);
    }

    public function create(User $user): bool
    {
        return $this->hasPremiumPlan($user);
    }

    public function delete(User $user, BeepCall $beepCall): bool
    {
        return $this->hasPremiumPlan($user)
            && $this->ownsOrganizationId($user, $beepCall->organization_id);
    }

    public function callNow(User $user, BeepCall $beepCall): bool
    {
        return $this->delete($user, $beepCall);
    }

    public function uploadAudio(User $user): bool
    {
        return $this->hasPremiumPlan($user);
    }
}

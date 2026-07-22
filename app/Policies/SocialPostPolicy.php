<?php

namespace App\Policies;

use App\Models\SocialPost;
use App\Models\User;
use App\Policies\Concerns\BelongsToOrganization;

class SocialPostPolicy
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

    public function update(User $user, SocialPost $post): bool
    {
        return $this->ownsOrganizationId($user, $post->organization_id);
    }

    public function delete(User $user, SocialPost $post): bool
    {
        return $this->update($user, $post);
    }

    public function publishNow(User $user, SocialPost $post): bool
    {
        return $this->update($user, $post);
    }
}

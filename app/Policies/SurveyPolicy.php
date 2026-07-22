<?php

namespace App\Policies;

use App\Models\Survey;
use App\Models\User;
use App\Policies\Concerns\BelongsToOrganization;

class SurveyPolicy
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

    public function update(User $user, Survey $survey): bool
    {
        return $this->ownsOrganizationId($user, $survey->organization_id);
    }

    public function delete(User $user, Survey $survey): bool
    {
        return $this->update($user, $survey);
    }

    public function viewResponses(User $user, Survey $survey): bool
    {
        return $this->update($user, $survey);
    }
}

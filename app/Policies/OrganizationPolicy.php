<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

final class OrganizationPolicy
{
    use HandlesAuthorization;

    public function update(User $user, Organization $organization): bool
    {
        return $organization->users()->wherePivot('user_id', $user->id)->exists();
    }
}

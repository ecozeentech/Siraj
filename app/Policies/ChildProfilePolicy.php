<?php

namespace App\Policies;

use App\Models\Family\ChildProfile;
use App\Models\User;

class ChildProfilePolicy
{
    /** Child data is only accessible to the parent account that owns the family. */
    public function manage(User $user, ChildProfile $child): bool
    {
        return $child->family()->where('owner_id', $user->id)->exists();
    }
}

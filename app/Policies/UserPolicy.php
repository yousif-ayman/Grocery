<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function manageData(User $user, User $targetUser): bool // users can manage their own data, but not other users' data
    {
        return $user->is($targetUser);
    }
}
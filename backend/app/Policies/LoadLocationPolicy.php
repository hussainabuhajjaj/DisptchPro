<?php

namespace App\Policies;

use App\Models\User;
use App\Support\Auth\RoleGuard;
use Illuminate\Auth\Access\HandlesAuthorization;

class LoadLocationPolicy
{
    use HandlesAuthorization;

    public function viewAny(?User $user): bool
    {
        return RoleGuard::hasOpsAccess($user);
    }

    public function view(?User $user): bool
    {
        return RoleGuard::hasOpsAccess($user);
    }
}

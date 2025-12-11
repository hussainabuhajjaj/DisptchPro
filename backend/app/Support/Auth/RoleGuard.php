<?php

namespace App\Support\Auth;

use Illuminate\Foundation\Auth\User as Authenticatable;

class RoleGuard
{
    /**
     * Determine if the user has admin/staff style access.
     */
    public static function hasOpsAccess(?Authenticatable $user): bool
    {
        if (!$user) {
            return false;
        }

        if (method_exists($user, 'hasAnyRole')) {
            return $user->hasAnyRole(['admin', 'staff']);
        }

        // Fallback: allow when no role system is present.
        return true;
    }
}

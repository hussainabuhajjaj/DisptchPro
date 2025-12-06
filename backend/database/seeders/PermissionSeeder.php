<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'manage users',
            'manage roles',
            'manage permissions',
            'manage loads',
            'manage carriers',
            'manage clients',
            'manage drivers',
            'manage bookings',
            'manage invoices',
            'manage settlements',
            'manage landing',
            'manage media',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(
                ['name' => $perm, 'guard_name' => 'web'],
                ['name' => $perm, 'guard_name' => 'web']
            );
        }

        $adminRole = Role::firstOrCreate(
            ['name' => 'admin', 'guard_name' => 'web'],
            ['name' => 'admin', 'guard_name' => 'web']
        );

        $adminRole->syncPermissions($permissions);

        // Attach current primary admin user (id=1) to admin role if exists
        $user = User::find(1);
        if ($user) {
            $user->syncRoles([$adminRole]);
        }
    }
}

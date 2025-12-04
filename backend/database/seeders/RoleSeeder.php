<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App\Models\User;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = ['Super Admin', 'Dispatcher', 'Accounting', 'Sales', 'Read-Only'];
        foreach ($roles as $role) {
            Role::findOrCreate($role);
        }

        $user = User::firstOrCreate(
            ['email' => 'admin@hadispatch.com'],
            ['name' => 'Super Admin', 'password' => Hash::make('password')]
        );

        $user->syncRoles(['Super Admin']);
    }
}

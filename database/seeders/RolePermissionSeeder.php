<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $dateTime = now();
        Role::query()->insert([
            [
                'name' => 'Super Admin',
                'guard_name' => 'web',
                'created_at' => $dateTime,
                'updated_at' => $dateTime
            ],
            [
                'name' => 'Receptionist',
                'guard_name' => 'web',
                'created_at' => $dateTime,
                'updated_at' => $dateTime
            ],
            [
                'name' => 'Employee',
                'guard_name' => 'web',
                'created_at' => $dateTime,
                'updated_at' => $dateTime,
            ],
            [
                'name' => 'Visitor',
                'guard_name' => 'web',
                'created_at' => $dateTime,
                'updated_at' => $dateTime
            ]
        ]);
    }
}

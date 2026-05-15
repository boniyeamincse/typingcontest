<?php

namespace Database\Seeders;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

        // Create permissions
        Permission::upsert([
            ['name' => 'manage_contests', 'guard_name' => 'api'],
            ['name' => 'manage_users', 'guard_name' => 'api'],
            ['name' => 'manage_badges', 'guard_name' => 'api'],
            ['name' => 'view_analytics', 'guard_name' => 'api'],
            ['name' => 'manage_anti_cheat', 'guard_name' => 'api'],
            ['name' => 'access_pro_features', 'guard_name' => 'api'],
        ], ['name', 'guard_name']);

        // Create roles
        Role::upsert([
            ['name' => 'admin', 'guard_name' => 'api'],
            ['name' => 'pro_user', 'guard_name' => 'api'],
            ['name' => 'free_user', 'guard_name' => 'api'],
        ], ['name', 'guard_name']);

        // Assign permissions to roles
        $adminRole = Role::where('name', 'admin')->firstOrFail();
        $adminRole->syncPermissions([
            'manage_contests',
            'manage_users',
            'manage_badges',
            'view_analytics',
            'manage_anti_cheat',
        ]);

        $proRole = Role::where('name', 'pro_user')->firstOrFail();
        $proRole->syncPermissions([
            'access_pro_features',
        ]);

        // free_user intentionally has no elevated permissions
    }
}

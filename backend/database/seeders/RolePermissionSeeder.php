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
        ], ['name', 'guard_name']);

        // Create roles
        Role::upsert([
            ['name' => 'admin', 'guard_name' => 'api'],
            ['name' => 'moderator', 'guard_name' => 'api'],
            ['name' => 'user', 'guard_name' => 'api'],
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

        $moderatorRole = Role::where('name', 'moderator')->firstOrFail();
        $moderatorRole->syncPermissions([
            'manage_contests',
            'view_analytics',
            'manage_anti_cheat',
        ]);

        // User role has no permissions by default
    }
}

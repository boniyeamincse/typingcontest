<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()['cache']->forget('spatie.permission.cache');

        $permissions = [
            'admin.dashboard.view',
            'admin.users.manage',
            'admin.contests.manage',
            'admin.live.monitor',
            'admin.content.manage',
            'admin.subscriptions.manage',
            'admin.payments.manage',
            'admin.badges.manage',
            'admin.leaderboard.manage',
            'admin.reports.view',
            'admin.security.manage',
            'admin.support.manage',
            'admin.cms.manage',
            'admin.notifications.send',
            'admin.system.monitor',
            'admin.logs.view',
            'admin.api.manage',
            'admin.roles.manage',
            'manage_contests',
            'manage_users',
            'manage_badges',
            'view_analytics',
            'manage_anti_cheat',
            'access_pro_features',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'api');
        }

        $roles = [
            'super_admin',
            'contest_admin',
            'user_moderator',
            'support_admin',
            'content_manager',
            'admin',
            'pro_user',
            'free_user',
        ];

        foreach ($roles as $role) {
            Role::findOrCreate($role, 'api');
        }

        Role::findByName('super_admin', 'api')->syncPermissions($permissions);

        Role::findByName('contest_admin', 'api')->syncPermissions([
            'admin.dashboard.view',
            'admin.contests.manage',
            'admin.live.monitor',
            'admin.subscriptions.manage',
            'admin.payments.manage',
            'admin.leaderboard.manage',
            'admin.reports.view',
            'admin.notifications.send',
            'admin.system.monitor',
            'admin.logs.view',
            'admin.api.manage',
            'manage_contests',
            'view_analytics',
            'manage_anti_cheat',
        ]);

        Role::findByName('user_moderator', 'api')->syncPermissions([
            'admin.dashboard.view',
            'admin.users.manage',
            'admin.security.manage',
            'admin.logs.view',
            'manage_users',
            'manage_anti_cheat',
        ]);

        Role::findByName('support_admin', 'api')->syncPermissions([
            'admin.dashboard.view',
            'admin.support.manage',
            'admin.notifications.send',
            'admin.reports.view',
            'admin.logs.view',
        ]);

        Role::findByName('content_manager', 'api')->syncPermissions([
            'admin.dashboard.view',
            'admin.content.manage',
            'admin.cms.manage',
            'admin.notifications.send',
            'admin.badges.manage',
            'manage_badges',
        ]);

        Role::findByName('admin', 'api')->syncPermissions([
            'manage_contests',
            'manage_users',
            'manage_badges',
            'view_analytics',
            'manage_anti_cheat',
            'admin.reports.view',
            'admin.payments.manage',
            'admin.subscriptions.manage',
        ]);

        Role::findByName('pro_user', 'api')->syncPermissions(['access_pro_features']);
        Role::findByName('free_user', 'api')->syncPermissions([]);
    }
}

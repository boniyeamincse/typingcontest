<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create roles and permissions first
        $this->call(RolePermissionSeeder::class);

        // Create badges
        $this->call(BadgeSeeder::class);

        // Create typing texts
        $this->call(TypingTextSeeder::class);

        // Create predictable test accounts
        $this->call(TestUserSeeder::class);

        // Create test admin user if not exists
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'username' => 'admin',
                'country' => 'US',
                'plan_type' => 'pro',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
            ]
        );

        if (! $admin->hasRole('super_admin')) {
            $admin->syncRoles(['super_admin', 'admin']);
        }

        // Create test users (up to 10 more)
        $existingCount = User::count();
        if ($existingCount < 11) {
            User::factory(11 - $existingCount)->create()->each(function (User $user): void {
                $user->assignRole('free_user');
            });
        }

        User::where('email', '!=', 'admin@example.com')->get()->each(function (User $user): void {
            if ($user->roles()->doesntExist()) {
                $user->assignRole('free_user');
            }
        });
    }
}

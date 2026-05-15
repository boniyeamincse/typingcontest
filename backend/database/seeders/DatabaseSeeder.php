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

        // Create test admin user if not exists
        if (!User::where('email', 'admin@example.com')->exists()) {
            User::factory()->create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'username' => 'admin',
                'country' => 'US',
                'plan_type' => 'pro',
            ])->assignRole('admin');
        }

        // Create test users (up to 10 more)
        $existingCount = User::count();
        if ($existingCount < 11) {
            User::factory(11 - $existingCount)->create();
        }
    }
}

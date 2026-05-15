<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name'     => 'Robert Schaefer',
                'username' => 'rschaefer',
                'email'    => 'rschaefer@example.com',
            ],
            ['name' => 'User 3',  'username' => 'user3',  'email' => 'user3@example.com'],
            ['name' => 'User 4',  'username' => 'user4',  'email' => 'user4@example.com'],
            ['name' => 'User 5',  'username' => 'user5',  'email' => 'user5@example.com'],
            ['name' => 'User 6',  'username' => 'user6',  'email' => 'user6@example.com'],
            ['name' => 'User 7',  'username' => 'user7',  'email' => 'user7@example.com'],
            ['name' => 'User 8',  'username' => 'user8',  'email' => 'user8@example.com'],
            ['name' => 'User 9',  'username' => 'user9',  'email' => 'user9@example.com'],
            ['name' => 'User 10', 'username' => 'user10', 'email' => 'user10@example.com'],
            ['name' => 'User 11', 'username' => 'user11', 'email' => 'user11@example.com'],
        ];

        foreach ($users as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'              => $data['name'],
                    'username'          => $data['username'],
                    'country'           => 'US',
                    'plan_type'         => 'free',
                    'email_verified_at' => now(),
                    'password'          => Hash::make('password'),
                ]
            );

            if ($user->roles()->doesntExist()) {
                $user->assignRole('free_user');
            }

            $this->command->line("  <info>✓</info> {$data['email']}");
        }
    }
}

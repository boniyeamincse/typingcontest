<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        Badge::upsert([
            [
                'name' => 'Speed Demon',
                'slug' => 'speed-demon',
                'icon_url' => 'https://cdn.example.com/badges/speed-demon.png',
                'description' => 'Achieve 150+ WPM in a contest',
                'requirement_type' => 'wpm',
                'requirement_value' => 150,
                'is_premium' => false,
            ],
            [
                'name' => 'Accuracy Master',
                'slug' => 'accuracy-master',
                'icon_url' => 'https://cdn.example.com/badges/accuracy-master.png',
                'description' => 'Achieve 99%+ accuracy',
                'requirement_type' => 'accuracy',
                'requirement_value' => 99,
                'is_premium' => false,
            ],
            [
                'name' => 'Marathon Runner',
                'slug' => 'marathon-runner',
                'icon_url' => 'https://cdn.example.com/badges/marathon-runner.png',
                'description' => 'Complete 50 contests',
                'requirement_type' => 'contest_count',
                'requirement_value' => 50,
                'is_premium' => false,
            ],
            [
                'name' => 'Champion',
                'slug' => 'champion',
                'icon_url' => 'https://cdn.example.com/badges/champion.png',
                'description' => 'Rank #1 globally',
                'requirement_type' => 'rank',
                'requirement_value' => 1,
                'is_premium' => true,
            ],
            [
                'name' => 'Pro Subscriber',
                'slug' => 'pro-subscriber',
                'icon_url' => 'https://cdn.example.com/badges/pro-subscriber.png',
                'description' => 'Subscribe to Pro plan',
                'requirement_type' => 'special',
                'requirement_value' => 0,
                'is_premium' => true,
            ],
        ], ['slug']);
    }
}

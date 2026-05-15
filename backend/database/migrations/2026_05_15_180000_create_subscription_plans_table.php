<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('code', 32)->unique();
            $table->enum('tier', ['free', 'pro', 'vip'])->index();
            $table->enum('billing_cycle', ['monthly', 'yearly', 'lifetime'])->default('monthly');
            $table->decimal('price', 10, 2)->default(0);
            $table->string('currency', 8)->default('BDT');
            $table->unsignedInteger('duration_days')->default(30);
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->index(['tier', 'billing_cycle']);
        });

        DB::table('subscription_plans')->insert([
            [
                'name' => 'Free Plan',
                'code' => 'free_monthly',
                'tier' => 'free',
                'billing_cycle' => 'monthly',
                'price' => 0,
                'currency' => 'BDT',
                'duration_days' => 30,
                'features' => json_encode([
                    'limited_contest_access' => true,
                    'ads_enabled' => true,
                    'basic_stats' => true,
                    'limited_leaderboard_access' => true,
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pro Monthly',
                'code' => 'pro_monthly',
                'tier' => 'pro',
                'billing_cycle' => 'monthly',
                'price' => 199,
                'currency' => 'BDT',
                'duration_days' => 30,
                'features' => json_encode([
                    'unlimited_contest_access' => true,
                    'advanced_analytics' => true,
                    'no_ads' => true,
                    'multiplayer_mode' => true,
                    'priority_leaderboard_sync' => true,
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pro Yearly',
                'code' => 'pro_yearly',
                'tier' => 'pro',
                'billing_cycle' => 'yearly',
                'price' => 1999,
                'currency' => 'BDT',
                'duration_days' => 365,
                'features' => json_encode([
                    'unlimited_contest_access' => true,
                    'advanced_analytics' => true,
                    'no_ads' => true,
                    'multiplayer_mode' => true,
                    'priority_leaderboard_sync' => true,
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'VIP Monthly',
                'code' => 'vip_monthly',
                'tier' => 'vip',
                'billing_cycle' => 'monthly',
                'price' => 499,
                'currency' => 'BDT',
                'duration_days' => 30,
                'features' => json_encode([
                    'exclusive_tournaments' => true,
                    'ai_coach' => true,
                    'vip_leaderboard' => true,
                    'special_rewards' => true,
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};

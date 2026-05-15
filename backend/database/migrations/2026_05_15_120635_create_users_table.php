<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add new columns to existing users table
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'username')) {
                $table->string('username')->unique()->after('name');
            }
            if (!Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable()->after('username');
            }
            if (!Schema::hasColumn('users', 'country')) {
                $table->string('country', 2)->default('US')->after('avatar'); // ISO 3166-1
            }
            if (!Schema::hasColumn('users', 'plan_type')) {
                $table->enum('plan_type', ['free', 'pro'])->default('free')->after('country');
            }
            if (!Schema::hasColumn('users', 'subscription_status')) {
                $table->enum('subscription_status', ['active', 'expired', 'cancelled'])->default('active')->after('plan_type');
            }
            if (!Schema::hasColumn('users', 'subscription_end_date')) {
                $table->timestamp('subscription_end_date')->nullable()->after('subscription_status');
            }
            if (!Schema::hasColumn('users', 'xp_points')) {
                $table->unsignedBigInteger('xp_points')->default(0)->after('subscription_end_date');
            }
            if (!Schema::hasColumn('users', 'global_rank')) {
                $table->unsignedBigInteger('global_rank')->nullable()->after('xp_points');
            }
            if (!Schema::hasColumn('users', 'total_wpm')) {
                $table->unsignedInteger('total_wpm')->default(0)->after('global_rank');
            }
            if (!Schema::hasColumn('users', 'accuracy_avg')) {
                $table->decimal('accuracy_avg', 5, 2)->default(0)->after('total_wpm');
            }
            if (!Schema::hasColumn('users', 'is_banned')) {
                $table->boolean('is_banned')->default(false)->after('accuracy_avg');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'username', 'avatar', 'country', 'plan_type', 'subscription_status',
                'subscription_end_date', 'xp_points', 'global_rank', 'total_wpm', 'accuracy_avg', 'is_banned'
            ]);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', [
                'login',
                'logout',
                'profile_update',
                'avatar_upload',
                'contest_join',
                'contest_complete',
                'badge_unlock',
                'subscription_upgrade',
                'rank_change',
            ]);
            $table->string('description');
            $table->json('metadata')->nullable();  // contextual data per activity type
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'created_at']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_activities');
    }
};

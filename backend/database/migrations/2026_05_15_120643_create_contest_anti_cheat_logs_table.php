<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contest_anti_cheat_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contest_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('event_type', ['tab_switch', 'paste_attempt', 'suspicious_wpm', 'ai_flag', 'timing_anomaly'])->default('ai_flag');
            $table->json('metadata')->nullable();
            $table->timestamp('logged_at');
            $table->index(['contest_id', 'user_id']);
            $table->index('event_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contest_anti_cheat_logs');
    }
};

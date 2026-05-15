<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('typing_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('contest_id')->constrained('contests')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->uuid('session_uuid')->unique();
            $table->enum('status', ['pending', 'countdown', 'active', 'submitted', 'expired', 'disqualified'])
                ->default('pending')
                ->index();
            $table->unsignedInteger('duration_seconds')->default(60);
            $table->timestamp('countdown_started_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('submitted_at')->nullable();
            $table->unsignedSmallInteger('countdown_seconds')->default(3);
            $table->boolean('auto_submitted')->default(false);
            $table->boolean('is_flagged')->default(false);
            $table->string('disqualified_reason', 255)->nullable();
            $table->unsignedInteger('last_sequence')->default(0);
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('device_fingerprint', 255)->nullable();
            $table->timestamps();

            $table->unique(['contest_id', 'user_id']);
            $table->index(['contest_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index(['contest_id', 'updated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('typing_sessions');
    }
};

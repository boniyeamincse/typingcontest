<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contest_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contest_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('session_token', 64)->unique();
            $table->enum('status', ['waiting', 'typing', 'submitted', 'disqualified'])->default('waiting');
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamp('started_typing_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->json('keystroke_data')->nullable();     // timing analytics
            $table->unsignedTinyInteger('tab_switches')->default(0);
            $table->unsignedTinyInteger('paste_attempts')->default(0);
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('device_fingerprint', 64)->nullable();
            $table->boolean('is_flagged')->default(false);
            $table->timestamps();

            $table->unique(['contest_id', 'user_id']);
            $table->index(['contest_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contest_sessions');
    }
};

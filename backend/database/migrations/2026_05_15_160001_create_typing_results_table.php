<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('typing_results', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('typing_session_id')->constrained('typing_sessions')->cascadeOnDelete();
            $table->foreignId('contest_id')->constrained('contests')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->unsignedInteger('correct_words')->default(0);
            $table->unsignedInteger('correct_characters')->default(0);
            $table->unsignedInteger('total_characters')->default(0);
            $table->unsignedInteger('errors')->default(0);
            $table->unsignedInteger('cpm')->default(0);
            $table->unsignedInteger('wpm')->default(0);
            $table->decimal('accuracy', 5, 2)->default(0);
            $table->decimal('score', 10, 2)->default(0);
            $table->unsignedInteger('progress_percent')->default(0);
            $table->unsignedInteger('duration_seconds')->default(0);

            $table->boolean('is_disqualified')->default(false);
            $table->string('disqualified_reason', 255)->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique('typing_session_id');
            $table->index(['contest_id', 'score']);
            $table->index(['contest_id', 'wpm']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('typing_results');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_scores', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('contest_id')->nullable()->constrained('contests')->nullOnDelete();
            $table->foreignId('typing_result_id')->nullable()->constrained('typing_results')->nullOnDelete();
            $table->decimal('score', 10, 2)->default(0)->index();
            $table->unsignedInteger('wpm')->default(0);
            $table->decimal('accuracy', 6, 2)->default(0);
            $table->unsignedInteger('errors')->default(0);
            $table->unsignedInteger('completion_time_ms')->default(0);
            $table->decimal('bonus_points', 10, 2)->default(0);
            $table->decimal('fast_finish_bonus', 10, 2)->default(0);
            $table->decimal('perfect_accuracy_bonus', 10, 2)->default(0);
            $table->decimal('winning_bonus', 10, 2)->default(0);
            $table->string('anomaly_flag', 64)->nullable()->index();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['contest_id', 'score']);
            $table->index(['user_id', 'created_at']);
            $table->index(['created_at', 'score']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_scores');
    }
};

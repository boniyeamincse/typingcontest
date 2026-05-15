<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('contest_rankings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('contest_id')->constrained('contests')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('typing_result_id')->nullable()->constrained('typing_results')->nullOnDelete();
            $table->unsignedInteger('rank')->default(0)->index();
            $table->unsignedInteger('previous_rank')->nullable();
            $table->integer('rank_movement')->default(0);
            $table->decimal('score', 10, 2)->default(0)->index();
            $table->unsignedInteger('wpm')->default(0);
            $table->decimal('accuracy', 6, 2)->default(0);
            $table->unsignedInteger('errors')->default(0);
            $table->unsignedInteger('completion_time_ms')->default(0);
            $table->decimal('bonus_points', 10, 2)->default(0);
            $table->enum('medal', ['gold', 'silver', 'bronze'])->nullable();
            $table->timestamps();

            $table->unique(['contest_id', 'user_id']);
            $table->index(['contest_id', 'rank']);
            $table->index(['contest_id', 'score']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contest_rankings');
    }
};

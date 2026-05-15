<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ranking_history', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['global', 'contest', 'daily', 'weekly', 'monthly', 'country'])->index();
            $table->string('period_key', 32)->nullable()->index();
            $table->char('country_code', 2)->nullable()->index();
            $table->foreignId('contest_id')->nullable()->constrained('contests')->nullOnDelete();
            $table->unsignedInteger('rank')->default(0);
            $table->unsignedInteger('previous_rank')->nullable();
            $table->integer('rank_movement')->default(0);
            $table->decimal('score', 10, 2)->default(0);
            $table->unsignedInteger('wpm')->default(0);
            $table->decimal('accuracy', 6, 2)->default(0);
            $table->unsignedInteger('errors')->default(0);
            $table->unsignedInteger('completion_time_ms')->default(0);
            $table->json('meta')->nullable();
            $table->timestamp('recorded_at')->useCurrent()->index();
            $table->timestamps();

            $table->index(['type', 'period_key', 'recorded_at']);
            $table->index(['contest_id', 'recorded_at']);
            $table->index(['user_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ranking_history');
    }
};

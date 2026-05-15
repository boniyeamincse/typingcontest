<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rankings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['global', 'daily', 'weekly', 'monthly', 'country'])->index();
            $table->string('period_key', 32)->nullable()->index();
            $table->char('country_code', 2)->nullable()->index();
            $table->unsignedInteger('rank')->default(0)->index();
            $table->unsignedInteger('previous_rank')->nullable();
            $table->integer('rank_movement')->default(0);
            $table->decimal('total_score', 14, 2)->default(0)->index();
            $table->unsignedInteger('total_matches')->default(0);
            $table->unsignedInteger('wins')->default(0);
            $table->unsignedInteger('avg_wpm')->default(0);
            $table->decimal('avg_accuracy', 6, 2)->default(0);
            $table->unsignedInteger('avg_errors')->default(0);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'type', 'period_key', 'country_code'], 'rankings_user_scope_unique');
            $table->index(['type', 'period_key', 'rank']);
            $table->index(['type', 'country_code', 'rank']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rankings');
    }
};

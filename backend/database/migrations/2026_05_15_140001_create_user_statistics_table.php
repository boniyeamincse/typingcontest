<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_statistics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedInteger('avg_wpm')->default(0);
            $table->unsignedInteger('highest_wpm')->default(0);
            $table->decimal('avg_accuracy', 5, 2)->default(0);
            $table->unsignedInteger('total_matches')->default(0);
            $table->unsignedInteger('total_wins')->default(0);
            $table->unsignedInteger('typing_streak')->default(0);      // current streak in days
            $table->unsignedInteger('longest_streak')->default(0);
            $table->unsignedInteger('typing_minutes')->default(0);     // total typing time in minutes
            $table->unsignedBigInteger('total_points')->default(0);
            $table->unsignedBigInteger('weekly_rank')->nullable();
            $table->unsignedBigInteger('monthly_rank')->nullable();
            $table->unsignedBigInteger('country_rank')->nullable();
            $table->date('streak_last_date')->nullable();               // last date user participated
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_statistics');
    }
};

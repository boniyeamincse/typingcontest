<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_daily_snapshots', function (Blueprint $table) {
            $table->id();
            $table->date('snapshot_date')->unique();
            $table->unsignedInteger('active_users')->default(0);
            $table->unsignedInteger('matches_played')->default(0);
            $table->decimal('avg_wpm', 8, 2)->default(0);
            $table->decimal('revenue', 12, 2)->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['snapshot_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_daily_snapshots');
    }
};

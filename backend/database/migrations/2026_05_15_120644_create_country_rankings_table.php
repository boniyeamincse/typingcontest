<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('country_rankings', function (Blueprint $table) {
            $table->id();
            $table->string('country_code', 2)->unique(); // ISO 3166-1
            $table->string('country_name');
            $table->decimal('total_score', 15, 2)->default(0);
            $table->decimal('avg_wpm', 8, 2)->default(0);
            $table->unsignedInteger('participant_count')->default(0);
            $table->unsignedInteger('rank')->nullable();
            $table->string('period_key'); // e.g., 'global', '2026-05-15'
            $table->timestamps();
            $table->unique(['country_code', 'period_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('country_rankings');
    }
};

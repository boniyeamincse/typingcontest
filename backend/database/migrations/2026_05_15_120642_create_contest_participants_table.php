<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Skip if results table already exists - use it instead
        if (!Schema::hasTable('contest_participants')) {
            if (Schema::hasTable('results')) {
                // Rename results to contest_participants if needed
                Schema::rename('results', 'contest_participants');
            } else {
                Schema::create('contest_participants', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('contest_id')->constrained()->cascadeOnDelete();
                    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                    $table->unsignedInteger('wpm')->default(0);
                    $table->decimal('accuracy', 5, 2)->default(0);
                    $table->unsignedInteger('errors')->default(0);
                    $table->decimal('score', 10, 2)->default(0);
                    $table->unsignedInteger('rank')->nullable();
                    $table->timestamp('submitted_at')->nullable();
                    $table->timestamps();
                    $table->unique(['user_id', 'contest_id']);
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('contest_participants')) {
            Schema::dropIfExists('contest_participants');
        }
    }
};

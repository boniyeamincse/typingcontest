<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leaderboards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['global', 'daily', 'weekly', 'monthly', 'country'])->default('global');
            $table->string('period_key'); // e.g., '2026-05-15', '2026-W20', '2026-05'
            $table->unsignedInteger('rank');
            $table->decimal('score', 10, 2);
            $table->unsignedInteger('wpm');
            $table->decimal('accuracy', 5, 2);
            $table->timestamps();
            $table->unique(['type', 'period_key', 'user_id']);
            $table->index(['type', 'period_key', 'rank']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leaderboards');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contest_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('wpm')->default(0);
            $table->decimal('accuracy', 5, 2)->default(0);
            $table->unsignedInteger('errors')->default(0);
            $table->decimal('score', 10, 2)->storedAs('ROUND((wpm * accuracy / 100) - errors, 2)');
            $table->unsignedInteger('rank')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'contest_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('results');
    }
};

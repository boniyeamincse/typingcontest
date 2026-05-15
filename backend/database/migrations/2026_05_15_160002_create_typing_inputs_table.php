<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('typing_inputs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('typing_session_id')->constrained('typing_sessions')->cascadeOnDelete();
            $table->unsignedInteger('sequence')->default(0);
            $table->unsignedInteger('cursor_position')->default(0);
            $table->unsignedInteger('correct_characters')->default(0);
            $table->unsignedInteger('total_characters')->default(0);
            $table->unsignedInteger('errors')->default(0);
            $table->unsignedInteger('progress_percent')->default(0);
            $table->json('payload')->nullable();
            $table->timestamp('captured_at')->useCurrent();
            $table->timestamps();

            $table->unique(['typing_session_id', 'sequence']);
            $table->index(['typing_session_id', 'captured_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('typing_inputs');
    }
};

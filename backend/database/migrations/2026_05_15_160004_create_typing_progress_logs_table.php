<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('typing_progress_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('typing_session_id')->constrained('typing_sessions')->cascadeOnDelete();
            $table->unsignedInteger('sequence')->default(0);
            $table->unsignedInteger('elapsed_ms')->default(0);
            $table->unsignedInteger('wpm')->default(0);
            $table->unsignedInteger('cpm')->default(0);
            $table->decimal('accuracy', 5, 2)->default(0);
            $table->unsignedInteger('errors')->default(0);
            $table->unsignedInteger('progress_percent')->default(0);
            $table->json('snapshot')->nullable();
            $table->timestamp('logged_at')->useCurrent();
            $table->timestamps();

            $table->index(['typing_session_id', 'sequence']);
            $table->index(['typing_session_id', 'logged_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('typing_progress_logs');
    }
};

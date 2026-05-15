<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('typing_errors', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('typing_session_id')->constrained('typing_sessions')->cascadeOnDelete();
            $table->unsignedInteger('sequence')->default(0);
            $table->unsignedInteger('word_index')->default(0);
            $table->unsignedInteger('char_index')->default(0);
            $table->string('expected_char', 8)->nullable();
            $table->string('typed_char', 8)->nullable();
            $table->string('error_type', 50)->default('mismatch')->index();
            $table->timestamp('detected_at')->useCurrent();
            $table->timestamps();

            $table->index(['typing_session_id', 'sequence']);
            $table->index(['typing_session_id', 'detected_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('typing_errors');
    }
};

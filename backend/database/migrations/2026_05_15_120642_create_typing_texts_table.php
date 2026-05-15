<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('typing_texts', function (Blueprint $table) {
            $table->id();
            $table->text('content');
            $table->string('language')->default('en');
            $table->unsignedInteger('word_count');
            $table->enum('difficulty', ['easy', 'medium', 'hard', 'extreme'])->default('medium');
            $table->string('source_label')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('typing_texts');
    }
};

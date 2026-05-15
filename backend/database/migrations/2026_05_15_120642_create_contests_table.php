<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Skip if contests table already exists - add missing columns instead
        if (!Schema::hasTable('contests')) {
            Schema::create('contests', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('slug')->unique();
                $table->enum('type', ['daily', 'weekly', 'monthly', 'special'])->default('daily');
                $table->enum('status', ['draft', 'published', 'active', 'finished'])->default('draft');
                $table->unsignedInteger('max_participants')->default(1000);
                $table->text('prize_description')->nullable();
                $table->unsignedBigInteger('typing_text_id')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamp('start_time');
                $table->timestamp('end_time');
                $table->foreign('typing_text_id')->references('id')->on('typing_texts')->onDelete('set null');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
                $table->timestamps();
            });
        } else {
            // Add missing columns to existing contests table
            Schema::table('contests', function (Blueprint $table) {
                if (!Schema::hasColumn('contests', 'slug')) {
                    $table->string('slug')->unique()->after('title');
                }
                if (!Schema::hasColumn('contests', 'max_participants')) {
                    $table->unsignedInteger('max_participants')->default(1000)->after('status');
                }
                if (!Schema::hasColumn('contests', 'prize_description')) {
                    $table->text('prize_description')->nullable()->after('max_participants');
                }
                if (!Schema::hasColumn('contests', 'typing_text_id')) {
                    $table->unsignedBigInteger('typing_text_id')->nullable()->after('prize_description');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('contests')) {
            Schema::table('contests', function (Blueprint $table) {
                $table->dropColumn(['slug', 'max_participants', 'prize_description', 'typing_text_id']);
            });
        }
    }
};

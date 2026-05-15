<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contests', function (Blueprint $table) {
            if (!Schema::hasColumn('contests', 'type')) {
                $table->enum('type', ['daily', 'weekly', 'monthly', 'tournament', 'special'])
                      ->default('daily')->after('slug');
            } else {
                // Extend existing enum to include 'tournament'
                $table->enum('type', ['daily', 'weekly', 'monthly', 'tournament', 'special'])
                      ->default('daily')->change();
            }

            if (!Schema::hasColumn('contests', 'duration_minutes')) {
                $table->unsignedTinyInteger('duration_minutes')->default(3)->after('type');
            }
            if (!Schema::hasColumn('contests', 'allow_late_join')) {
                $table->boolean('allow_late_join')->default(false)->after('duration_minutes');
            }
            if (!Schema::hasColumn('contests', 'text_content')) {
                $table->text('text_content')->nullable()->after('allow_late_join');
            }
            if (!Schema::hasColumn('contests', 'started_at')) {
                $table->timestamp('started_at')->nullable()->after('end_time');
            }
            if (!Schema::hasColumn('contests', 'ended_at')) {
                $table->timestamp('ended_at')->nullable()->after('started_at');
            }
            if (!Schema::hasColumn('contests', 'is_paused')) {
                $table->boolean('is_paused')->default(false)->after('ended_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('contests', function (Blueprint $table) {
            $table->dropColumn([
                'duration_minutes', 'allow_late_join', 'text_content',
                'started_at', 'ended_at', 'is_paused',
            ]);
        });
    }
};

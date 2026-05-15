<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('leaderboards', function (Blueprint $table): void {
            if (!Schema::hasColumn('leaderboards', 'contest_id')) {
                $table->foreignId('contest_id')->nullable()->after('user_id')->constrained('contests')->nullOnDelete();
            }

            if (!Schema::hasColumn('leaderboards', 'country_code')) {
                $table->char('country_code', 2)->nullable()->after('period_key')->index();
            }

            if (!Schema::hasColumn('leaderboards', 'previous_rank')) {
                $table->unsignedInteger('previous_rank')->nullable()->after('rank');
            }

            if (!Schema::hasColumn('leaderboards', 'rank_movement')) {
                $table->integer('rank_movement')->default(0)->after('previous_rank');
            }

            if (!Schema::hasColumn('leaderboards', 'medal')) {
                $table->enum('medal', ['gold', 'silver', 'bronze'])->nullable()->after('rank_movement');
            }

            if (!Schema::hasColumn('leaderboards', 'errors')) {
                $table->unsignedInteger('errors')->default(0)->after('accuracy');
            }

            if (!Schema::hasColumn('leaderboards', 'completion_time_ms')) {
                $table->unsignedInteger('completion_time_ms')->default(0)->after('errors');
            }

            if (!Schema::hasColumn('leaderboards', 'bonus_points')) {
                $table->decimal('bonus_points', 10, 2)->default(0)->after('completion_time_ms');
            }

            if (!Schema::hasColumn('leaderboards', 'meta')) {
                $table->json('meta')->nullable()->after('bonus_points');
            }

            $table->index(['contest_id', 'rank']);
            $table->index(['country_code', 'rank']);
            $table->index(['type', 'score']);
        });
    }

    public function down(): void
    {
        Schema::table('leaderboards', function (Blueprint $table): void {
            $table->dropIndex(['contest_id', 'rank']);
            $table->dropIndex(['country_code', 'rank']);
            $table->dropIndex(['type', 'score']);

            foreach (['contest_id', 'country_code', 'previous_rank', 'rank_movement', 'medal', 'errors', 'completion_time_ms', 'bonus_points', 'meta'] as $column) {
                if (Schema::hasColumn('leaderboards', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

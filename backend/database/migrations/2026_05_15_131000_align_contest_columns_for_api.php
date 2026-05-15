<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('contests')) {
            return;
        }

        Schema::table('contests', function (Blueprint $table) {
            if (! Schema::hasColumn('contests', 'start_time')) {
                $table->timestamp('start_time')->nullable()->after('ends_at');
            }

            if (! Schema::hasColumn('contests', 'end_time')) {
                $table->timestamp('end_time')->nullable()->after('start_time');
            }
        });

        DB::statement('ALTER TABLE contests MODIFY text_content TEXT NULL');

        if (Schema::hasColumn('contests', 'starts_at')) {
            DB::statement('UPDATE contests SET start_time = COALESCE(start_time, starts_at)');
        }

        if (Schema::hasColumn('contests', 'ends_at')) {
            DB::statement('UPDATE contests SET end_time = COALESCE(end_time, ends_at)');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('contests')) {
            return;
        }

        if (Schema::hasColumn('contests', 'start_time') && Schema::hasColumn('contests', 'starts_at')) {
            DB::statement('UPDATE contests SET starts_at = COALESCE(starts_at, start_time)');
        }

        if (Schema::hasColumn('contests', 'end_time') && Schema::hasColumn('contests', 'ends_at')) {
            DB::statement('UPDATE contests SET ends_at = COALESCE(ends_at, end_time)');
        }

        DB::statement('ALTER TABLE contests MODIFY text_content TEXT NOT NULL');

        Schema::table('contests', function (Blueprint $table) {
            if (Schema::hasColumn('contests', 'end_time')) {
                $table->dropColumn('end_time');
            }

            if (Schema::hasColumn('contests', 'start_time')) {
                $table->dropColumn('start_time');
            }
        });
    }
};

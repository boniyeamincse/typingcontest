<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('contests')) {
            return;
        }

        DB::statement("ALTER TABLE contests MODIFY status ENUM('draft', 'published', 'active', 'completed', 'finished', 'cancelled') DEFAULT 'draft'");

        DB::table('contests')
            ->where('status', 'completed')
            ->update(['status' => 'finished']);

        DB::statement("ALTER TABLE contests MODIFY status ENUM('draft', 'published', 'active', 'finished', 'cancelled') DEFAULT 'draft'");
    }

    public function down(): void
    {
        if (! Schema::hasTable('contests')) {
            return;
        }

        DB::table('contests')
            ->where('status', 'finished')
            ->update(['status' => 'completed']);

        DB::statement("ALTER TABLE contests MODIFY status ENUM('draft', 'published', 'active', 'completed') DEFAULT 'draft'");
    }
};

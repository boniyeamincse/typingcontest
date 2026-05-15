<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contest_participants', function (Blueprint $table) {
            if (!Schema::hasColumn('contest_participants', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable()->after('rank');
            }
        });
    }

    public function down(): void
    {
        Schema::table('contest_participants', function (Blueprint $table) {
            $table->dropColumn('submitted_at');
        });
    }
};

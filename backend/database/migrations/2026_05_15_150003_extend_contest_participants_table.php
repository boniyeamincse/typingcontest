<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contest_participants', function (Blueprint $table) {
            if (!Schema::hasColumn('contest_participants', 'joined_at')) {
                $table->timestamp('joined_at')->nullable()->after('rank');
            }
            if (!Schema::hasColumn('contest_participants', 'is_disqualified')) {
                $table->boolean('is_disqualified')->default(false)->after('joined_at');
            }
            if (!Schema::hasColumn('contest_participants', 'disqualified_reason')) {
                $table->string('disqualified_reason')->nullable()->after('is_disqualified');
            }
        });
    }

    public function down(): void
    {
        Schema::table('contest_participants', function (Blueprint $table) {
            $table->dropColumn(['joined_at', 'is_disqualified', 'disqualified_reason']);
        });
    }
};

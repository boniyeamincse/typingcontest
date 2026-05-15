<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add ip_address and session_token tracing to anti-cheat logs
        Schema::table('contest_anti_cheat_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('contest_anti_cheat_logs', 'ip_address')) {
                $table->string('ip_address', 45)->nullable()->after('event_type');
            }
            if (!Schema::hasColumn('contest_anti_cheat_logs', 'severity')) {
                $table->enum('severity', ['low', 'medium', 'high'])->default('low')->after('ip_address');
            }
            if (!Schema::hasColumn('contest_anti_cheat_logs', 'auto_flagged')) {
                $table->boolean('auto_flagged')->default(false)->after('severity');
            }
        });

        // Extend event_type enum to cover new types
        DB::statement("ALTER TABLE contest_anti_cheat_logs 
            MODIFY event_type ENUM(
                'tab_switch','paste_attempt','suspicious_wpm','ai_flag',
                'timing_anomaly','multiple_sessions','device_change','rapid_correction'
            ) NOT NULL DEFAULT 'ai_flag'");
    }

    public function down(): void
    {
        Schema::table('contest_anti_cheat_logs', function (Blueprint $table) {
            $table->dropColumn(['ip_address', 'severity', 'auto_flagged']);
        });
    }
};

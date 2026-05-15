<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contest_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contest_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('duration_minutes')->default(3);    // 1 | 3 | 5
            $table->unsignedSmallInteger('max_wpm_threshold')->default(250); // flag as suspicious above this
            $table->boolean('allow_paste')->default(false);
            $table->boolean('allow_late_join')->default(false);
            $table->unsignedSmallInteger('late_join_grace_seconds')->default(0);
            $table->boolean('auto_submit_on_timeout')->default(true);
            $table->boolean('anti_cheat_enabled')->default(true);
            $table->boolean('track_keystrokes')->default(true);
            $table->unsignedSmallInteger('max_tab_switches')->default(3);   // flag after N switches
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contest_rules');
    }
};

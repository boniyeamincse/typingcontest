<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->text('bio')->nullable();
            $table->string('cover_photo')->nullable();
            $table->json('social_links')->nullable(); // {twitter, github, linkedin, website}
            $table->foreignId('featured_badge_id')->nullable()->constrained('badges')->nullOnDelete();
            $table->unsignedTinyInteger('level')->default(1);
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_online')->default(false);
            $table->timestamp('last_seen_at')->nullable();
            // Privacy settings
            $table->boolean('show_email')->default(false);
            $table->boolean('show_activity')->default(true);
            $table->boolean('show_match_history')->default(true);
            $table->boolean('show_stats')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};

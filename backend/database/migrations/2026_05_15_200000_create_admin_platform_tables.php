<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'banned_reason')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->string('banned_reason')->nullable()->after('is_banned');
            });
        }

        if (! Schema::hasColumn('users', 'suspended_until')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->timestamp('suspended_until')->nullable()->after('banned_reason');
            });
        }

        if (! Schema::hasTable('admin_activity_logs')) {
            Schema::create('admin_activity_logs', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
                $table->string('module');
                $table->string('action');
                $table->string('target_type')->nullable();
                $table->unsignedBigInteger('target_id')->nullable();
                $table->json('meta')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();
                $table->index(['module', 'action']);
                $table->index(['target_type', 'target_id']);
            });
        }

        if (! Schema::hasTable('support_tickets')) {
            Schema::create('support_tickets', function (Blueprint $table): void {
                $table->id();
                $table->string('ticket_no')->unique();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('assigned_admin_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('subject');
                $table->text('description');
                $table->enum('status', ['open', 'in_progress', 'closed', 'escalated'])->default('open');
                $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
                $table->json('evidence')->nullable();
                $table->timestamps();
                $table->index(['status', 'priority']);
            });
        }

        if (! Schema::hasTable('support_ticket_messages')) {
            Schema::create('support_ticket_messages', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('support_ticket_id')->constrained('support_tickets')->cascadeOnDelete();
                $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
                $table->text('message');
                $table->json('attachments')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('security_blocks')) {
            Schema::create('security_blocks', function (Blueprint $table): void {
                $table->id();
                $table->string('type');
                $table->string('value');
                $table->string('reason')->nullable();
                $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
                $table->timestamp('expires_at')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->unique(['type', 'value']);
            });
        }

        if (! Schema::hasTable('api_access_logs')) {
            Schema::create('api_access_logs', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('path');
                $table->string('method', 10);
                $table->unsignedSmallInteger('status_code');
                $table->integer('response_time_ms')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->json('meta')->nullable();
                $table->timestamps();
                $table->index(['status_code', 'created_at']);
            });
        }

        if (! Schema::hasTable('cms_pages')) {
            Schema::create('cms_pages', function (Blueprint $table): void {
                $table->id();
                $table->string('slug')->unique();
                $table->string('title');
                $table->longText('content')->nullable();
                $table->boolean('is_published')->default(false);
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('cms_banners')) {
            Schema::create('cms_banners', function (Blueprint $table): void {
                $table->id();
                $table->string('title');
                $table->string('image_url');
                $table->string('cta_text')->nullable();
                $table->string('cta_url')->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('admin_notifications')) {
            Schema::create('admin_notifications', function (Blueprint $table): void {
                $table->id();
                $table->string('title');
                $table->text('message');
                $table->json('channels')->nullable();
                $table->json('audience')->nullable();
                $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
                $table->timestamp('sent_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_notifications');
        Schema::dropIfExists('cms_banners');
        Schema::dropIfExists('cms_pages');
        Schema::dropIfExists('api_access_logs');
        Schema::dropIfExists('security_blocks');
        Schema::dropIfExists('support_ticket_messages');
        Schema::dropIfExists('support_tickets');
        Schema::dropIfExists('admin_activity_logs');

        if (Schema::hasColumn('users', 'suspended_until')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropColumn('suspended_until');
            });
        }

        if (Schema::hasColumn('users', 'banned_reason')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropColumn('banned_reason');
            });
        }
    }
};

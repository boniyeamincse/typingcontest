<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('plan', ['free', 'pro']);
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('currency')->default('USD');
            $table->string('payment_gateway')->nullable();
            $table->string('transaction_id')->nullable()->unique();
            $table->enum('status', ['active', 'pending', 'failed', 'cancelled', 'expired'])->default('active');
            $table->timestamp('started_at');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};

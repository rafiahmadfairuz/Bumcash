<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table): void {
            $table->id();
            $table->uuid('internal_id')->unique();
            $table->foreignId('tagihan_id')->constrained('tagihan')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('provider', 80)->nullable();
            $table->string('provider_transaction_id', 150)->nullable()->index();
            $table->decimal('amount', 12, 2);
            $table->char('currency', 3)->default('IDR');
            $table->string('status', 20)->default('pending')->index();
            $table->string('payment_method', 80)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('idempotency_key', 100);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['tagihan_id', 'idempotency_key']);
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('payment_webhook_events', function (Blueprint $table): void {
            $table->id();
            $table->string('provider', 80);
            $table->string('event_key', 191);
            $table->string('event_type', 100);
            $table->string('payment_reference', 100)->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->unique(['provider', 'event_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_webhook_events');
        Schema::dropIfExists('payment_transactions');
    }
};

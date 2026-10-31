<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('qoyod_syncs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trendyol_order_id')->constrained('trendyol_orders')->cascadeOnDelete();
            $table->string('type', 30)->index(); // 'invoice', 'credit_note'
            $table->string('qoyod_id')->nullable()->index();
            $table->string('qoyod_reference')->nullable()->index();
            $table->unsignedBigInteger('claim_id')->nullable()->index();
            $table->string('status', 20)->default('pending')->index(); // 'pending', 'success', 'failed'
            $table->unsignedInteger('attempts')->default(0);
            $table->text('last_error')->nullable();
            $table->timestamp('synced_at')->nullable()->index();
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->timestamps();

            // Guarantee idempotency: only one sync record per order per type
            $table->unique(['trendyol_order_id', 'type'], 'order_sync_type_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qoyod_syncs');
    }
};

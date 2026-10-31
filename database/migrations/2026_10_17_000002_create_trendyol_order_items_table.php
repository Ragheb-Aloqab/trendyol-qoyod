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
        Schema::create('trendyol_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trendyol_order_id')->constrained('trendyol_orders')->cascadeOnDelete();
            $table->unsignedBigInteger('line_item_id')->nullable()->index();
            $table->string('barcode')->nullable()->index();
            $table->string('merchant_sku')->nullable()->index();
            $table->string('product_code')->nullable();
            $table->string('product_name');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('vat_rate', 5, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->string('currency', 10)->default('TRY');
            $table->json('raw_payload')->nullable();
            $table->timestamps();

            $table->index(['trendyol_order_id', 'line_item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trendyol_order_items');
    }
};

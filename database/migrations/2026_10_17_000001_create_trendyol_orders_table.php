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
        Schema::create('trendyol_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->index();
            $table->unsignedBigInteger('shipment_package_id')->unique();
            $table->string('packet_number')->nullable()->index();
            $table->string('status')->index();
            $table->string('currency', 10)->default('TRY');
            $table->decimal('gross_amount', 12, 2)->default(0);
            $table->decimal('total_discount', 12, 2)->default(0);
            $table->decimal('total_price', 12, 2)->default(0);

            $table->string('customer_first_name')->nullable();
            $table->string('customer_last_name')->nullable();
            $table->string('customer_email')->nullable()->index();
            $table->string('customer_phone')->nullable();
            $table->string('tax_number')->nullable()->index();
            $table->string('tax_office')->nullable();
            $table->boolean('is_commercial')->default(false);

            $table->json('invoice_address')->nullable();
            $table->json('shipment_address')->nullable();
            $table->json('raw_payload');

            $table->timestamp('order_date')->nullable()->index();
            $table->timestamp('package_last_modified_date')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trendyol_orders');
    }
};

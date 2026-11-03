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
        Schema::create('qoyod_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trendyol_order_id')->constrained('trendyol_orders')->cascadeOnDelete();
            $table->foreignId('qoyod_sync_id')->nullable()->constrained('qoyod_syncs')->nullOnDelete();
            $table->string('qoyod_invoice_id', 50)->index();
            $table->string('qoyod_invoice_number', 100)->nullable()->index();
            $table->date('issue_date')->nullable()->index();
            $table->date('due_date')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0.00); // المبلغ قبل الضريبة
            $table->decimal('tax_amount', 12, 2)->default(0.00); // قيمة الضريبة
            $table->decimal('total_discount', 12, 2)->default(0.00); // قيمة الخصم
            $table->decimal('total', 12, 2)->default(0.00); // إجمالي الفاتورة بعد الضريبة
            $table->string('status', 30)->default('Approved')->index();
            $table->text('pdf_url')->nullable();
            $table->string('pdf_path')->nullable();
            $table->string('fetch_status', 20)->default('success')->index(); // 'pending', 'success', 'failed'
            $table->text('fetch_error')->nullable();
            $table->timestamp('fetched_at')->nullable()->index();
            $table->json('raw_data')->nullable();
            $table->timestamps();

            $table->unique(['qoyod_invoice_id'], 'qoyod_invoice_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qoyod_invoices');
    }
};

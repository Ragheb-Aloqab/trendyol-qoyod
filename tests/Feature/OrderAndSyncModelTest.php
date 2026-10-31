<?php

namespace Tests\Feature;

use App\Models\QoyodSync;
use App\Models\TrendyolOrder;
use App\Models\TrendyolOrderItem;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderAndSyncModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_trendyol_order_with_items(): void
    {
        $order = TrendyolOrder::create([
            'order_number' => 'TY-100200300',
            'shipment_package_id' => 99887766,
            'packet_number' => 'PK-99887766',
            'status' => 'Shipped',
            'currency' => 'TRY',
            'gross_amount' => 150.00,
            'total_discount' => 15.00,
            'total_price' => 135.00,
            'customer_first_name' => 'Ahmed',
            'customer_last_name' => 'Ali',
            'customer_email' => 'ahmed@example.com',
            'raw_payload' => ['sample' => 'data'],
            'order_date' => now(),
        ]);

        $item = $order->items()->create([
            'line_item_id' => 12345,
            'barcode' => '8680001234567',
            'merchant_sku' => 'SKU-001',
            'product_name' => 'Wireless Mouse',
            'quantity' => 1,
            'unit_price' => 150.00,
            'vat_rate' => 15.00,
            'discount' => 15.00,
            'currency' => 'TRY',
        ]);

        $this->assertDatabaseHas('trendyol_orders', [
            'id' => $order->id,
            'shipment_package_id' => 99887766,
            'status' => 'Shipped',
        ]);

        $this->assertDatabaseHas('trendyol_order_items', [
            'id' => $item->id,
            'barcode' => '8680001234567',
            'trendyol_order_id' => $order->id,
        ]);

        $this->assertEquals('Ahmed Ali', $order->customer_full_name);
        $this->assertTrue($order->isApprovedForSync());
    }

    public function test_duplicate_shipment_package_id_is_prevented(): void
    {
        TrendyolOrder::create([
            'order_number' => 'TY-100',
            'shipment_package_id' => 11223344,
            'status' => 'Created',
            'raw_payload' => [],
        ]);

        $this->expectException(QueryException::class);

        // Attempt duplicate package ID
        TrendyolOrder::create([
            'order_number' => 'TY-101',
            'shipment_package_id' => 11223344,
            'status' => 'Created',
            'raw_payload' => [],
        ]);
    }

    public function test_can_create_qoyod_sync_record_and_prevent_duplicate_invoice(): void
    {
        $order = TrendyolOrder::create([
            'order_number' => 'TY-200',
            'shipment_package_id' => 55667788,
            'status' => 'Shipped',
            'raw_payload' => [],
        ]);

        $sync = $order->syncs()->create([
            'type' => QoyodSync::TYPE_INVOICE,
            'qoyod_reference' => 'TY-55667788',
            'status' => QoyodSync::STATUS_PENDING,
        ]);

        $this->assertDatabaseHas('qoyod_syncs', [
            'id' => $sync->id,
            'trendyol_order_id' => $order->id,
            'type' => 'invoice',
            'status' => 'pending',
        ]);

        $sync->markAsSuccess('QOYOD-INV-999');
        $this->assertEquals(QoyodSync::STATUS_SUCCESS, $sync->fresh()->status);
        $this->assertEquals('QOYOD-INV-999', $sync->fresh()->qoyod_id);

        $this->expectException(QueryException::class);

        // Idempotency: Attempt to create a duplicate invoice sync for the same order
        $order->syncs()->create([
            'type' => QoyodSync::TYPE_INVOICE,
            'status' => QoyodSync::STATUS_PENDING,
        ]);
    }
}

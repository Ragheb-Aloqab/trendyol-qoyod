<?php

namespace Tests\Feature;

use App\Models\TrendyolOrder;
use App\Models\TrendyolOrderItem;
use App\Services\QoyodInvoiceSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class QoyodInvoiceSyncServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_payload_includes_is_inclusive_and_correct_vat_configuration(): void
    {
        config()->set('services.qoyod.base_url', 'https://api.qoyod.com/2.0');
        config()->set('services.qoyod.api_key', 'test_key');
        config()->set('services.qoyod.default_inventory_id', 1);

        $order = TrendyolOrder::create([
            'order_number' => 'TY-TEST-VAT-01',
            'shipment_package_id' => 88877766,
            'status' => 'Delivered',
            'currency' => 'SAR',
            'gross_amount' => 177.21,
            'total_discount' => 34.55,
            'total_price' => 142.66,
            'customer_first_name' => 'Sara',
            'customer_last_name' => 'Ahmed',
            'customer_phone' => '0501234567',
            'order_date' => now(),
            'raw_payload' => [],
        ]);

        $order->items()->create([
            'line_item_id' => 101,
            'barcode' => 'BAR-101',
            'merchant_sku' => 'SKU-101',
            'product_name' => 'Black Dress',
            'quantity' => 1,
            'unit_price' => 142.66,
            'vat_rate' => 15.00,
            'discount' => 34.55,
            'currency' => 'SAR',
            'raw_payload' => [
                'lineGrossAmount' => 177.21,
                'lineTotalDiscount' => 34.55,
                'amount' => 177.21,
                'discount' => 34.55,
                'price' => 142.66,
            ],
        ]);

        Http::fake([
            'https://api.qoyod.com/2.0/invoices?q*' => Http::response(['invoices' => []], 200),
            'https://api.qoyod.com/2.0/customers*' => Http::response(['customers' => [['id' => 5]]], 200),
            'https://api.qoyod.com/2.0/products*' => Http::response(['products' => [['id' => 10]]], 200),
            'https://api.qoyod.com/2.0/invoices' => Http::response([
                'invoice' => [
                    'id' => 9999,
                    'reference' => 'TY-TEST-VAT-01',
                    'total' => '142.66',
                    'status' => 'Approved',
                ],
            ], 201),
        ]);

        $service = new QoyodInvoiceSyncService();
        $result = $service->syncOrderInvoice($order);

        $this->assertTrue($result['success']);
        $this->assertEquals('9999', $result['qoyod_id']);

        Http::assertSent(function ($request) {
            if ($request->url() !== 'https://api.qoyod.com/2.0/invoices' || $request->method() !== 'POST') {
                return false;
            }

            $body = $request->data();
            $invoice = $body['invoice'] ?? [];
            $line = $invoice['line_items'][0] ?? [];

            // 1. Must have is_inclusive: false on invoice level
            $invoiceInclusive = ($invoice['is_inclusive'] ?? false) === false;

            // 2. Must have is_inclusive: false on line item level
            $lineInclusive = ($line['is_inclusive'] ?? false) === false;

            // 3. Must have original gross retail unit price (177.21), total line discount (34.55), and 0% tax
            $unitPriceMatches = (float) $line['unit_price'] === 177.21;
            $discountMatches = (float) $line['discount'] === 34.55;
            $discountTypeMatches = ($line['discount_type'] ?? '') === 'amount';
            $taxPercentMatches = (float) ($line['tax_percent'] ?? 0) === 0.0;

            return $invoiceInclusive && $lineInclusive && $unitPriceMatches && $discountMatches && $discountTypeMatches && $taxPercentMatches;
        });
    }

    public function test_multi_quantity_line_items_calculate_discount_and_gross_accurately(): void
    {
        config()->set('services.qoyod.base_url', 'https://api.qoyod.com/2.0');
        config()->set('services.qoyod.api_key', 'test_key');
        config()->set('services.qoyod.default_inventory_id', 1);

        $order = TrendyolOrder::create([
            'order_number' => 'TY-TEST-VAT-02',
            'shipment_package_id' => 88877767,
            'status' => 'Delivered',
            'currency' => 'SAR',
            'gross_amount' => 598.00,
            'total_discount' => 142.32,
            'total_price' => 455.68,
            'customer_first_name' => 'Fatima',
            'customer_last_name' => 'Ali',
            'customer_phone' => '0509876543',
            'order_date' => now(),
            'raw_payload' => [],
        ]);

        $order->items()->create([
            'line_item_id' => 202,
            'barcode' => 'BAR-202',
            'merchant_sku' => 'SKU-202',
            'product_name' => 'Abaya',
            'quantity' => 2,
            'unit_price' => 227.84,
            'vat_rate' => 15.00,
            'discount' => 71.16,
            'currency' => 'SAR',
            'raw_payload' => [
                'lineGrossAmount' => 299.00,
                'lineTotalDiscount' => 71.16,
                'amount' => 299.00,
                'discount' => 71.16,
                'price' => 227.84,
            ],
        ]);

        Http::fake([
            'https://api.qoyod.com/2.0/invoices?q*' => Http::response(['invoices' => []], 200),
            'https://api.qoyod.com/2.0/customers*' => Http::response(['customers' => [['id' => 6]]], 200),
            'https://api.qoyod.com/2.0/products*' => Http::response(['products' => [['id' => 11]]], 200),
            'https://api.qoyod.com/2.0/invoices' => Http::response([
                'invoice' => [
                    'id' => 10001,
                    'reference' => 'TY-TEST-VAT-02',
                    'total' => '455.68',
                    'status' => 'Approved',
                ],
            ], 201),
        ]);

        $service = new QoyodInvoiceSyncService();
        $result = $service->syncOrderInvoice($order);

        $this->assertTrue($result['success']);

        Http::assertSent(function ($request) {
            if ($request->url() !== 'https://api.qoyod.com/2.0/invoices' || $request->method() !== 'POST') {
                return false;
            }

            $body = $request->data();
            $line = $body['invoice']['line_items'][0] ?? [];

            // Quantity is 2, gross unit price is 299.00, total line discount is 142.32, tax_percent is 0
            return (float) $line['quantity'] === 2.0
                && (float) $line['unit_price'] === 299.00
                && (float) $line['discount'] === 142.32
                && $line['discount_type'] === 'amount'
                && (float) ($line['tax_percent'] ?? 0) === 0.0
                && ($line['is_inclusive'] ?? false) === false;
        });
    }
}

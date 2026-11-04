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

    public function test_invoice_payload_includes_is_inclusive_and_15_percent_vat(): void
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
                    'line_items' => [
                        [
                            'quantity' => '1.0',
                            'unit_price' => '154.0956521739',
                            'inclusive_unit_price' => '177.21',
                            'discount_amount' => '30.04',
                            'tax_percent' => '15.0',
                            'unrounded_vat_value' => '18.61',
                            'line_total' => '142.66',
                            'is_inclusive' => true,
                        ],
                    ],
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
            $invoiceInclusive = ($invoice['is_inclusive'] ?? true) === false;

            // 2. Line item does not require is_inclusive (invoice level handles it)
            $lineDoesNotHaveInclusive = !isset($line['is_inclusive']);

            // 3. Must have original gross retail unit price (177.21), total line discount (34.55), and 15% tax
            $unitPriceMatches = (float) $line['unit_price'] === 177.21;
            $discountMatches = (float) $line['discount'] === 34.55;
            $discountTypeMatches = ($line['discount_type'] ?? '') === 'amount';
            $taxPercentMatches = (float) ($line['tax_percent'] ?? 0) === 15.0;

            return $invoiceInclusive && $lineDoesNotHaveInclusive && $unitPriceMatches && $discountMatches && $discountTypeMatches && $taxPercentMatches;
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
                    'line_items' => [
                        [
                            'quantity' => '2.0',
                            'unit_price' => '260.0',
                            'inclusive_unit_price' => '299.0',
                            'discount_amount' => '123.76',
                            'tax_percent' => '15.0',
                            'unrounded_vat_value' => '59.44',
                            'line_total' => '455.68',
                            'is_inclusive' => true,
                        ],
                    ],
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

            // Quantity is 2, gross unit price is 299.00, total line discount is 142.32 (71.16 * 2), tax_percent is 15.0
            return (float) $line['quantity'] === 2.0
                && (float) $line['unit_price'] === 299.00
                && (float) $line['discount'] === 142.32
                && $line['discount_type'] === 'amount'
                && (float) ($line['tax_percent'] ?? 0) === 15.0
                && ($body['invoice']['is_inclusive'] ?? true) === false;
        });
    }

    public function test_exact_calculation_example_from_user_requirements(): void
    {
        // Product with:
        // Original gross price: 320.00 SAR
        // Discount: 85.40 SAR
        // Final VAT-inclusive price: 234.60 SAR
        // Expected Qoyod result:
        // Net amount (Ex-VAT): 204.00 SAR
        // VAT (15%): 30.60 SAR
        // Final invoice total: 234.60 SAR

        config()->set('services.qoyod.base_url', 'https://api.qoyod.com/2.0');
        config()->set('services.qoyod.api_key', 'test_key');
        config()->set('services.qoyod.default_inventory_id', 1);

        $order = TrendyolOrder::create([
            'order_number' => 'TY-CALC-320',
            'shipment_package_id' => 99911122,
            'status' => 'Delivered',
            'currency' => 'SAR',
            'gross_amount' => 320.00,
            'total_discount' => 85.40,
            'total_price' => 234.60,
            'customer_first_name' => 'Mohammed',
            'customer_last_name' => 'Khalid',
            'customer_phone' => '0555555555',
            'order_date' => now(),
            'raw_payload' => [],
        ]);

        $order->items()->create([
            'line_item_id' => 303,
            'barcode' => 'BAR-303',
            'merchant_sku' => 'SKU-303',
            'product_name' => 'Sample Product',
            'quantity' => 1,
            'unit_price' => 234.60,
            'vat_rate' => 15.00,
            'discount' => 85.40,
            'currency' => 'SAR',
            'raw_payload' => [
                'lineGrossAmount' => 320.00,
                'lineTotalDiscount' => 85.40,
                'amount' => 320.00,
                'discount' => 85.40,
                'price' => 234.60,
            ],
        ]);

        Http::fake([
            'https://api.qoyod.com/2.0/invoices?q*' => Http::response(['invoices' => []], 200),
            'https://api.qoyod.com/2.0/customers*' => Http::response(['customers' => [['id' => 7]]], 200),
            'https://api.qoyod.com/2.0/products*' => Http::response(['products' => [['id' => 12]]], 200),
            'https://api.qoyod.com/2.0/invoices' => Http::response([
                'invoice' => [
                    'id' => 10002,
                    'reference' => 'TY-CALC-320',
                    'total' => '234.60',
                    'status' => 'Approved',
                    'line_items' => [
                        [
                            'quantity' => '1.0',
                            'unit_price' => '278.260869565',
                            'inclusive_unit_price' => '320.0',
                            'discount_amount' => '74.260869565',
                            'tax_percent' => '15.0',
                            'unrounded_vat_value' => '30.60',
                            'line_total' => '234.60',
                            'is_inclusive' => true,
                        ],
                    ],
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
            $invoice = $body['invoice'] ?? [];
            $line = $invoice['line_items'][0] ?? [];

            // Verify payload fields: is_inclusive is false on invoice level
            $this->assertFalse($invoice['is_inclusive'] ?? true);
            $this->assertArrayNotHasKey('is_inclusive', $line);
            $this->assertEquals(320.00, (float) $line['unit_price']);
            $this->assertEquals(85.40, (float) $line['discount']);
            $this->assertEquals('amount', $line['discount_type']);
            $this->assertEquals(15.0, (float) $line['tax_percent']);

            return true;
        });
    }

    public function test_sync_automatically_fetches_and_persists_qoyod_invoice_and_pdf(): void
    {
        config()->set('services.qoyod.base_url', 'https://api.qoyod.com/2.0');
        config()->set('services.qoyod.api_key', 'test_key');
        config()->set('services.qoyod.default_inventory_id', 1);

        $order = TrendyolOrder::create([
            'order_number' => 'TY-FETCH-01',
            'shipment_package_id' => 1234567,
            'status' => 'Delivered',
            'currency' => 'SAR',
            'gross_amount' => 320.00,
            'total_discount' => 85.40,
            'total_price' => 234.60,
            'customer_first_name' => 'Fahad',
            'customer_last_name' => 'Al-Otaibi',
            'order_date' => now(),
            'raw_payload' => [],
        ]);

        $order->items()->create([
            'line_item_id' => 901,
            'barcode' => 'BAR-901',
            'merchant_sku' => 'SKU-901',
            'product_name' => 'Men Shirt',
            'quantity' => 1,
            'unit_price' => 234.60,
            'vat_rate' => 15.00,
            'discount' => 85.40,
            'currency' => 'SAR',
            'raw_payload' => [
                'lineGrossAmount' => 320.00,
                'lineTotalDiscount' => 85.40,
                'price' => 234.60,
            ],
        ]);

        Http::fake([
            'https://api.qoyod.com/2.0/invoices?q*' => Http::response(['invoices' => []], 200),
            'https://api.qoyod.com/2.0/customers*' => Http::response(['customers' => [['id' => 15]]], 200),
            'https://api.qoyod.com/2.0/products*' => Http::response(['products' => [['id' => 25]]], 200),
            'https://api.qoyod.com/2.0/invoices' => Http::response([
                'invoice' => [
                    'id' => 10745,
                    'reference' => 'TY-FETCH-01',
                    'total' => '234.60',
                    'status' => 'Approved',
                ],
            ], 201),
            'https://api.qoyod.com/2.0/invoices/10745' => Http::response([
                'invoice' => [
                    'id' => 10745,
                    'reference' => 'TY-FETCH-01',
                    'issue_date' => '2026-11-04',
                    'due_date' => '2026-11-04',
                    'status' => 'Approved',
                    'total' => '234.60',
                    'line_items' => [
                        [
                            'product_name' => 'Men Shirt',
                            'quantity' => '1.0',
                            'unit_price' => '278.26086956521739',
                            'discount_amount' => '74.26086956521739',
                            'tax_percent' => '15.0',
                            'unrounded_vat_value' => '30.60',
                            'line_total' => '234.60',
                        ],
                    ],
                ],
            ], 200),
            'https://api.qoyod.com/2.0/invoices/10745/pdf' => Http::response([
                'pdf_file' => 'https://cdn.qoyod.com/export/pdf/10745_invoice.pdf',
                'expire_at' => '2026-11-04T12:00:00Z',
            ], 200),
        ]);

        $service = new QoyodInvoiceSyncService();
        $result = $service->syncOrderInvoice($order);

        $this->assertTrue($result['success']);
        $this->assertEquals('10745', $result['qoyod_id']);

        // Assert QoyodInvoice record was automatically created in DB
        $storedInvoice = \App\Models\QoyodInvoice::where('qoyod_invoice_id', '10745')->first();
        $this->assertNotNull($storedInvoice);
        $this->assertEquals($order->id, $storedInvoice->trendyol_order_id);
        $this->assertEquals('TY-FETCH-01', $storedInvoice->qoyod_invoice_number);
        $this->assertEquals(204.00, (float) $storedInvoice->subtotal);
        $this->assertEquals(30.60, (float) $storedInvoice->tax_amount);
        $this->assertEquals(85.40, (float) $storedInvoice->total_discount);
        $this->assertEquals(234.60, (float) $storedInvoice->total);
        $this->assertEquals('Approved', $storedInvoice->status);
        $this->assertEquals('https://cdn.qoyod.com/export/pdf/10745_invoice.pdf', $storedInvoice->pdf_url);
        $this->assertEquals(\App\Models\QoyodInvoice::FETCH_STATUS_SUCCESS, $storedInvoice->fetch_status);
        $this->assertNull($storedInvoice->fetch_error);
    }

    public function test_fetch_failure_does_not_fail_invoice_creation_and_allows_retry(): void
    {
        config()->set('services.qoyod.base_url', 'https://api.qoyod.com/2.0');
        config()->set('services.qoyod.api_key', 'test_key');
        config()->set('services.qoyod.default_inventory_id', 1);

        $order = TrendyolOrder::create([
            'order_number' => 'TY-FETCH-ERR-02',
            'shipment_package_id' => 99887766,
            'status' => 'Delivered',
            'currency' => 'SAR',
            'gross_amount' => 100.00,
            'total_discount' => 0.00,
            'total_price' => 100.00,
            'order_date' => now(),
            'raw_payload' => [],
        ]);

        $order->items()->create([
            'line_item_id' => 902,
            'barcode' => 'BAR-902',
            'merchant_sku' => 'SKU-902',
            'product_name' => 'Sample Item',
            'quantity' => 1,
            'unit_price' => 100.00,
            'vat_rate' => 15.00,
            'discount' => 0.00,
            'currency' => 'SAR',
            'raw_payload' => ['lineGrossAmount' => 100.00, 'price' => 100.00],
        ]);

        $fetchSucceeds = false;

        Http::fake([
            'https://api.qoyod.com/2.0/invoices?q*' => Http::response(['invoices' => []], 200),
            'https://api.qoyod.com/2.0/customers*' => Http::response(['customers' => [['id' => 15]]], 200),
            'https://api.qoyod.com/2.0/products*' => Http::response(['products' => [['id' => 25]]], 200),
            'https://api.qoyod.com/2.0/invoices' => Http::response([
                'invoice' => [
                    'id' => 10746,
                    'reference' => 'TY-FETCH-ERR-02',
                    'total' => '100.00',
                ],
            ], 201),
            'https://api.qoyod.com/2.0/invoices/10746' => function () use (&$fetchSucceeds) {
                if (!$fetchSucceeds) {
                    return Http::response(['error' => 'Internal Server Error'], 500);
                }
                return Http::response([
                    'invoice' => [
                        'id' => 10746,
                        'reference' => 'TY-FETCH-ERR-02',
                        'issue_date' => '2026-11-04',
                        'status' => 'Approved',
                        'total' => '100.00',
                        'line_items' => [
                            [
                                'product_name' => 'Sample Item',
                                'quantity' => '1.0',
                                'unit_price' => '86.9565',
                                'discount_amount' => '0.00',
                                'tax_percent' => '15.0',
                                'unrounded_vat_value' => '13.04',
                                'line_total' => '100.00',
                            ],
                        ],
                    ],
                ], 200);
            },
            'https://api.qoyod.com/2.0/invoices/10746/pdf' => function () use (&$fetchSucceeds) {
                if (!$fetchSucceeds) {
                    return Http::response([], 500);
                }
                return Http::response([
                    'pdf_file' => 'https://cdn.qoyod.com/export/pdf/10746_recovered.pdf',
                ], 200);
            },
        ]);

        $service = new QoyodInvoiceSyncService();
        $result = $service->syncOrderInvoice($order);

        // Crucial requirement: Invoice creation in Qoyod must STILL succeed!
        $this->assertTrue($result['success']);
        $this->assertEquals('10746', $result['qoyod_id']);

        // Check QoyodInvoice record
        $storedInvoice = \App\Models\QoyodInvoice::where('qoyod_invoice_id', '10746')->first();
        $this->assertNotNull($storedInvoice);
        $this->assertEquals(\App\Models\QoyodInvoice::FETCH_STATUS_FAILED, $storedInvoice->fetch_status);
        $this->assertNotNull($storedInvoice->fetch_error);

        // Now simulate API recovery and retry fetching
        $fetchSucceeds = true;

        $retried = $service->fetchAndStoreInvoiceDetails('10746', $order, $order->invoiceSync);
        $this->assertNotNull($retried);
        $this->assertEquals(\App\Models\QoyodInvoice::FETCH_STATUS_SUCCESS, $retried->fetch_status);
        $this->assertNull($retried->fetch_error);
        $this->assertEquals('https://cdn.qoyod.com/export/pdf/10746_recovered.pdf', $retried->pdf_url);
    }
}

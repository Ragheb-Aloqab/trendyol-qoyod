<?php

namespace Tests\Feature;

use App\Models\TrendyolOrder;
use App\Models\TrendyolOrderItem;
use App\Services\TrendyolOrderSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TrendyolOrderSyncServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_service_fetches_and_persists_orders(): void
    {
        config()->set('services.trendyol.supplier_id', '1336083');
        config()->set('services.trendyol.api_key', 'test_key');
        config()->set('services.trendyol.api_secret', 'test_secret');
        config()->set('services.trendyol.base_url', 'https://apigw.trendyol.com');

        Http::fake([
            'https://apigw.trendyol.com/*' => Http::response([
                'page' => 0,
                'size' => 50,
                'totalElements' => 1,
                'totalPages' => 1,
                'content' => [
                    [
                        'id' => 99887711,
                        'shipmentPackageId' => 99887711,
                        'orderNumber' => 'TY-998877',
                        'shipmentNumber' => 'SH-998877',
                        'status' => 'Shipped',
                        'currencyCode' => 'SAR',
                        'grossAmount' => 250.00,
                        'totalDiscount' => 20.00,
                        'totalPrice' => 230.00,
                        'customerFirstName' => 'Fatima',
                        'customerLastName' => 'Al-Harbi',
                        'customerEmail' => 'fatima@example.com',
                        'orderDate' => 1730000000000,
                        'lastModifiedDate' => 1730000500000,
                        'lines' => [
                            [
                                'id' => 112233,
                                'barcode' => 'BAR-001',
                                'merchantSku' => 'SKU-001',
                                'productName' => 'Black Dress',
                                'quantity' => 1,
                                'lineUnitPrice' => 230.00,
                                'vatRate' => 15.00,
                                'lineTotalDiscount' => 20.00,
                                'currencyCode' => 'SAR',
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $service = new TrendyolOrderSyncService();
        $result = $service->syncOrders(maxPages: 1);

        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['total_fetched']);
        $this->assertEquals(1, $result['created']);

        $this->assertDatabaseHas('trendyol_orders', [
            'shipment_package_id' => 99887711,
            'order_number' => 'TY-998877',
            'status' => 'Shipped',
            'customer_first_name' => 'Fatima',
            'currency' => 'SAR',
        ]);

        $this->assertDatabaseHas('trendyol_order_items', [
            'line_item_id' => 112233,
            'barcode' => 'BAR-001',
            'product_name' => 'Black Dress',
        ]);
    }

    public function test_sync_service_sends_date_range_query_parameters(): void
    {
        config()->set('services.trendyol.supplier_id', '1336083');
        config()->set('services.trendyol.api_key', 'test_key');
        config()->set('services.trendyol.api_secret', 'test_secret');
        config()->set('services.trendyol.base_url', 'https://apigw.trendyol.com');

        Http::fake([
            'https://apigw.trendyol.com/*' => Http::response([
                'page' => 0,
                'size' => 50,
                'totalElements' => 0,
                'totalPages' => 0,
                'content' => [],
            ], 200),
        ]);

        $service = new TrendyolOrderSyncService();
        $service->syncOrders(
            maxPages: 1,
            startDate: '2026-10-01',
            endDate: '2026-10-15'
        );

        Http::assertSent(function ($request) {
            $url = $request->url();
            return str_contains($url, 'startDate=') && str_contains($url, 'endDate=');
        });
    }
}


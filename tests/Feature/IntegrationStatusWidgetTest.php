<?php

namespace Tests\Feature;

use App\Filament\Widgets\IntegrationStatusWidget;
use App\Filament\Widgets\SyncStatsOverviewWidget;
use App\Models\TrendyolOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IntegrationStatusWidgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_integration_widget_detects_missing_config(): void
    {
        config()->set('services.trendyol.api_key', null);
        config()->set('services.qoyod.api_key', null);

        $widget = new IntegrationStatusWidget();
        $widget->mount();

        $this->assertEquals('warning', $widget->trendyol['status']);
        $this->assertStringContainsString('غير مكتملة', $widget->trendyol['message']);

        $this->assertEquals('warning', $widget->qoyod['status']);
        $this->assertStringContainsString('غير محدد', $widget->qoyod['message']);
    }

    public function test_integration_widget_connects_to_trendyol_and_qoyod(): void
    {
        config()->set('services.trendyol.supplier_id', '123456');
        config()->set('services.trendyol.api_key', 'test_key');
        config()->set('services.trendyol.api_secret', 'test_secret');
        config()->set('services.trendyol.base_url', 'https://apigw.trendyol.com');

        config()->set('services.qoyod.api_key', 'qoyod_test_key');
        config()->set('services.qoyod.base_url', 'https://api.qoyod.com/2.0');

        Http::fake([
            'https://apigw.trendyol.com/*' => Http::response([
                'page' => 0,
                'size' => 1,
                'totalElements' => 42,
                'totalPages' => 42,
                'content' => [],
            ], 200),
            'https://api.qoyod.com/2.0/inventories' => Http::response([
                'inventories' => [
                    ['id' => 1, 'name' => 'Main Warehouse'],
                ],
            ], 200),
        ]);

        $widget = new IntegrationStatusWidget();
        $widget->mount();

        $this->assertTrue($widget->trendyol['connected']);
        $this->assertEquals('success', $widget->trendyol['status']);
        $this->assertEquals(42, $widget->trendyol['totalOrders']);

        $this->assertTrue($widget->qoyod['connected']);
        $this->assertEquals('success', $widget->qoyod['status']);
        $this->assertEquals(1, $widget->qoyod['inventoriesCount']);
    }

    public function test_integration_widget_diagnoses_errors_accurately(): void
    {
        config()->set('services.trendyol.supplier_id', '123456');
        config()->set('services.trendyol.api_key', 'invalid_key');
        config()->set('services.trendyol.api_secret', 'invalid_secret');
        config()->set('services.trendyol.base_url', 'https://apigw.trendyol.com');

        config()->set('services.qoyod.api_key', 'invalid_key');
        config()->set('services.qoyod.base_url', 'https://api.qoyod.com/2.0');

        Http::fake([
            'https://apigw.trendyol.com/*' => Http::response([
                'message' => 'Unauthorized: Invalid credentials provided',
            ], 401),
            'https://api.qoyod.com/2.0/inventories' => Http::response([
                'message' => 'API-KEY is invalid or expired',
            ], 401),
        ]);

        $widget = new IntegrationStatusWidget();
        $widget->mount();

        $this->assertFalse($widget->trendyol['connected']);
        $this->assertEquals('danger', $widget->trendyol['status']);
        $this->assertStringContainsString('Unauthorized', $widget->trendyol['error']);

        $this->assertFalse($widget->qoyod['connected']);
        $this->assertEquals('danger', $widget->qoyod['status']);
        $this->assertStringContainsString('invalid or expired', $widget->qoyod['error']);
    }
}

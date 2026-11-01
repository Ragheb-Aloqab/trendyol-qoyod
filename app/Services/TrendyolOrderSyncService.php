<?php

namespace App\Services;

use App\Models\QoyodSync;
use App\Models\TrendyolOrder;
use App\Models\TrendyolOrderItem;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class TrendyolOrderSyncService
{
    protected string $baseUrl;
    protected ?string $supplierId;
    protected ?string $apiKey;
    protected ?string $apiSecret;

    public function __construct()
    {
        $this->baseUrl = config('services.trendyol.base_url', 'https://apigw.trendyol.com');
        $this->supplierId = (string) config('services.trendyol.supplier_id');
        $this->apiKey = (string) config('services.trendyol.api_key');
        $this->apiSecret = (string) config('services.trendyol.api_secret');
    }

    /**
     * Fetch and synchronize orders from Trendyol into local database.
     *
     * @param int|null $maxPages Maximum number of pages to fetch (null = fetch all available pages)
     * @param int $pageSize Number of orders per page (default 50, max 200)
     * @param string|null $status Filter by specific status (e.g. Created, Shipped, Delivered)
     * @param mixed $startDate Filter by start date (epoch in milliseconds, seconds, Carbon, or Y-m-d string)
     * @param mixed $endDate Filter by end date (epoch in milliseconds, seconds, Carbon, or Y-m-d string)
     * @return array Summary of sync results
     */
    public function syncOrders(
        ?int $maxPages = null,
        int $pageSize = 50,
        ?string $status = null,
        mixed $startDate = null,
        mixed $endDate = null
    ): array {
        if (empty($this->supplierId) || empty($this->apiKey) || empty($this->apiSecret)) {
            throw new Exception('بيانات اتصال ترينديول غير مكتملة في ملف .env (Supplier ID, API Key, API Secret)');
        }

        if (empty($startDate)) {
            $startDate = \App\Models\AppSetting::get('sync_start_date', config('services.trendyol.sync_start_date', '2026-10-01'));
        }

        $startEpoch = $this->normalizeToMilliseconds($startDate, isEndOfDay: false);
        $endEpoch = $this->normalizeToMilliseconds($endDate, isEndOfDay: true);

        $totalFetched = 0;
        $createdCount = 0;
        $updatedCount = 0;
        $errors = [];

        $currentPage = 0;
        $totalPages = 1;

        do {
            $queryParams = [
                'page' => $currentPage,
                'size' => $pageSize,
                'orderByField' => 'PackageLastModifiedDate',
                'orderByDirection' => 'DESC',
            ];

            if (!empty($status)) {
                $queryParams['status'] = $status;
            }
            if ($startEpoch !== null) {
                $queryParams['startDate'] = $startEpoch;
            }
            if ($endEpoch !== null) {
                $queryParams['endDate'] = $endEpoch;
            }

            try {
                $response = Http::withBasicAuth($this->apiKey, $this->apiSecret)
                    ->withHeaders([
                        'User-Agent' => "{$this->supplierId} - SelfIntegration",
                        'Accept' => 'application/json',
                    ])
                    ->timeout(20)
                    ->get("{$this->baseUrl}/integration/order/sellers/{$this->supplierId}/orders", $queryParams);

                if (!$response->successful()) {
                    $errors[] = "فشل في الصفحة {$currentPage}: كود الاستجابة {$response->status()} - {$response->body()}";
                    Log::error("Trendyol sync page {$currentPage} failed", [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                    break;
                }

                $data = $response->json();
                $orders = $data['content'] ?? [];
                $totalPages = $data['totalPages'] ?? 1;

                if (empty($orders)) {
                    break;
                }

                foreach ($orders as $orderData) {
                    try {
                        $result = $this->saveOrder($orderData);
                        $totalFetched++;
                        if ($result === 'created') {
                            $createdCount++;
                        } else {
                            $updatedCount++;
                        }
                    } catch (Throwable $e) {
                        $packageId = $orderData['shipmentPackageId'] ?? $orderData['id'] ?? 'unknown';
                        $errors[] = "خطأ في معالجة الشحنة {$packageId}: " . $e->getMessage();
                        Log::error("Failed to save Trendyol order {$packageId}", [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                    }
                }

                $currentPage++;

                if ($maxPages !== null && $currentPage >= $maxPages) {
                    break;
                }
            } catch (Throwable $e) {
                $errors[] = "خطأ اتصال في الصفحة {$currentPage}: " . $e->getMessage();
                Log::error("Trendyol connection error during sync", ['error' => $e->getMessage()]);
                break;
            }
        } while ($currentPage < $totalPages);

        return [
            'success' => count($errors) === 0 || $totalFetched > 0,
            'total_fetched' => $totalFetched,
            'created' => $createdCount,
            'updated' => $updatedCount,
            'pages_processed' => $currentPage,
            'errors' => $errors,
        ];
    }

    /**
     * Save or update an individual order and its line items.
     */
    public function saveOrder(array $orderData): string
    {
        $packageId = (int) ($orderData['shipmentPackageId'] ?? $orderData['id']);

        if (!$packageId) {
            throw new Exception('لا يمكن حفظ الطلب بدون معرف الشحنة shipmentPackageId');
        }

        $orderDate = $this->parseTimestamp($orderData['orderDate'] ?? null);
        $modifiedDate = $this->parseTimestamp($orderData['lastModifiedDate'] ?? $orderData['packageLastModifiedDate'] ?? null);

        $orderNumber = (string) ($orderData['orderNumber'] ?? $packageId);
        $packetNumber = isset($orderData['shipmentNumber']) ? (string) $orderData['shipmentNumber'] : null;

        $status = (string) ($orderData['status'] ?? $orderData['shipmentPackageStatus'] ?? 'Created');
        $currency = (string) ($orderData['currencyCode'] ?? 'TRY');

        $grossAmount = (float) ($orderData['grossAmount'] ?? $orderData['packageGrossAmount'] ?? 0);
        $totalDiscount = (float) ($orderData['totalDiscount'] ?? $orderData['packageTotalDiscount'] ?? 0);
        $totalPrice = (float) ($orderData['totalPrice'] ?? $orderData['packageTotalPrice'] ?? 0);

        $firstName = $orderData['customerFirstName'] ?? $orderData['shipmentAddress']['firstName'] ?? null;
        $lastName = $orderData['customerLastName'] ?? $orderData['shipmentAddress']['lastName'] ?? null;
        $email = $orderData['customerEmail'] ?? null;
        $phone = $orderData['shipmentAddress']['phone'] ?? $orderData['invoiceAddress']['phone'] ?? null;

        $taxNumber = $orderData['taxNumber'] ?? $orderData['identityNumber'] ?? null;
        $taxOffice = $orderData['invoiceAddress']['taxOffice'] ?? null;
        $isCommercial = (bool) ($orderData['commercial'] ?? false);

        $invoiceAddress = $orderData['invoiceAddress'] ?? null;
        $shipmentAddress = $orderData['shipmentAddress'] ?? null;

        return DB::transaction(function () use (
            $packageId,
            $orderNumber,
            $packetNumber,
            $status,
            $currency,
            $grossAmount,
            $totalDiscount,
            $totalPrice,
            $firstName,
            $lastName,
            $email,
            $phone,
            $taxNumber,
            $taxOffice,
            $isCommercial,
            $invoiceAddress,
            $shipmentAddress,
            $orderData,
            $orderDate,
            $modifiedDate
        ) {
            $existing = TrendyolOrder::where('shipment_package_id', $packageId)->first();
            $action = $existing ? 'updated' : 'created';

            $order = TrendyolOrder::updateOrCreate(
                ['shipment_package_id' => $packageId],
                [
                    'order_number' => $orderNumber,
                    'packet_number' => $packetNumber,
                    'status' => $status,
                    'currency' => $currency,
                    'gross_amount' => $grossAmount,
                    'total_discount' => $totalDiscount,
                    'total_price' => $totalPrice,
                    'customer_first_name' => $firstName,
                    'customer_last_name' => $lastName,
                    'customer_email' => $email,
                    'customer_phone' => $phone,
                    'tax_number' => $taxNumber,
                    'tax_office' => $taxOffice,
                    'is_commercial' => $isCommercial,
                    'invoice_address' => $invoiceAddress,
                    'shipment_address' => $shipmentAddress,
                    'raw_payload' => $orderData,
                    'order_date' => $orderDate,
                    'package_last_modified_date' => $modifiedDate,
                ]
            );

            // Sync items
            $lines = $orderData['lines'] ?? [];
            if (!empty($lines)) {
                // Delete previous items to ensure clean state and prevent duplicate line items
                $order->items()->delete();

                $itemsToInsert = [];
                $now = now();

                foreach ($lines as $line) {
                    $lineItemId = $line['id'] ?? $line['lineId'] ?? $line['lineItemId'] ?? null;
                    $itemsToInsert[] = [
                        'trendyol_order_id' => $order->id,
                        'line_item_id' => $lineItemId,
                        'barcode' => $line['barcode'] ?? null,
                        'merchant_sku' => $line['merchantSku'] ?? $line['stockCode'] ?? $line['sku'] ?? null,
                        'product_code' => isset($line['productCode']) ? (string) $line['productCode'] : (isset($line['contentId']) ? (string) $line['contentId'] : null),
                        'product_name' => $line['productName'] ?? 'منتج ترينديول',
                        'quantity' => (int) ($line['quantity'] ?? 1),
                        'unit_price' => (float) ($line['lineUnitPrice'] ?? $line['price'] ?? $line['amount'] ?? 0),
                        'vat_rate' => (float) ($line['vatRate'] ?? 0),
                        'discount' => (float) ($line['lineTotalDiscount'] ?? $line['discount'] ?? 0),
                        'currency' => $line['currencyCode'] ?? $currency,
                        'raw_payload' => json_encode($line, JSON_UNESCAPED_UNICODE),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if (!empty($itemsToInsert)) {
                    TrendyolOrderItem::insert($itemsToInsert);
                }
            }

            // Ensure an invoice sync record exists if approved for sync
            if ($order->isApprovedForSync()) {
                $order->syncs()->firstOrCreate(
                    ['type' => QoyodSync::TYPE_INVOICE],
                    [
                        'status' => QoyodSync::STATUS_PENDING,
                        'qoyod_reference' => (string) $order->order_number,
                    ]
                );
            }

            return $action;
        });
    }

    /**
     * Parse epoch timestamp (in milliseconds or seconds) to Carbon.
     */
    protected function parseTimestamp(mixed $timestamp): ?Carbon
    {
        if (empty($timestamp)) {
            return null;
        }

        if (is_numeric($timestamp)) {
            $num = (float) $timestamp;
            // If greater than 10 digits, it's in milliseconds
            if ($num > 9999999999) {
                return Carbon::createFromTimestampMs($num);
            }
            return Carbon::createFromTimestamp((int) $num);
        }

        try {
            return Carbon::parse($timestamp);
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Convert various date formats (Y-m-d string, Carbon, timestamp in sec/ms) into epoch milliseconds.
     */
    public function normalizeToMilliseconds(mixed $date, bool $isEndOfDay = false): ?int
    {
        if (blank($date)) {
            return null;
        }

        if (is_numeric($date)) {
            $num = (int) $date;
            // If already milliseconds (> 10 digits)
            if ($num > 9999999999) {
                return $num;
            }
            return $num * 1000;
        }

        try {
            $carbon = Carbon::parse($date);
            if ($isEndOfDay) {
                $carbon = $carbon->endOfDay();
            } else {
                $carbon = $carbon->startOfDay();
            }

            return (int) ($carbon->getTimestamp() * 1000 + ($isEndOfDay ? 999 : 0));
        } catch (Throwable) {
            return null;
        }
    }
}


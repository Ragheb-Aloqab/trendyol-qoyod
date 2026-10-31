<?php

namespace App\Services;

use App\Models\QoyodSync;
use App\Models\TrendyolOrder;
use App\Models\TrendyolOrderItem;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class QoyodInvoiceSyncService
{
    protected string $baseUrl;
    protected ?string $apiKey;
    protected mixed $inventoryId;
    protected int $taxId;

    public function __construct()
    {
        $this->baseUrl = config('services.qoyod.base_url', 'https://api.qoyod.com/2.0');
        $this->apiKey = config('services.qoyod.api_key');
        $this->inventoryId = config('services.qoyod.default_inventory_id');
        $this->taxId = (int) config('services.qoyod.default_tax_id', 1);
    }

    /**
     * Send an invoice for a specific QoyodSync record to Qoyod API.
     */
    public function syncInvoice(QoyodSync $sync): array
    {
        if (empty($this->apiKey)) {
            $error = 'مفتاح QOYOD_API_KEY غير موجود في إعدادات ملف .env';
            $sync->markAsFailed($error);
            return ['success' => false, 'error' => $error];
        }

        $order = $sync->order;
        if (! $order) {
            $error = 'سجل الطلب المرتبط بعملية المزامنة غير موجود';
            $sync->markAsFailed($error);
            return ['success' => false, 'error' => $error];
        }

        // منع إرسال الفاتورة لقيود إن كان الطلب قبل تاريخ بدء المزامنة المعتمد (شهر 10)
        $startDate = \App\Models\AppSetting::get('sync_start_date', config('services.trendyol.sync_start_date', '2026-10-01'));
        if ($order->order_date && ! empty($startDate)) {
            $parsedStart = \Illuminate\Support\Carbon::parse($startDate)->startOfDay();
            if ($order->order_date->lt($parsedStart)) {
                $error = "تم منع المزامنة: تاريخ الطلب ({$order->order_date->format('Y-m-d')}) يسبق تاريخ بدء المزامنة المعتمد ({$startDate}).";
                $sync->markAsFailed($error);
                return ['success' => false, 'error' => $error];
            }
        }

        // Ensure order items are loaded
        $order->loadMissing('items');

        $headers = [
            'API-KEY' => $this->apiKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];

        $reference = $sync->qoyod_reference ?: ('TY-' . ($order->shipment_package_id ?: $order->order_number));

        try {
            // 1. Idempotency Check: Verify if invoice already exists in Qoyod by reference
            $existingCheck = Http::withHeaders($headers)
                ->timeout(10)
                ->get("{$this->baseUrl}/invoices", [
                    'q[reference_eq]' => $reference,
                ]);

            if ($existingCheck->successful()) {
                $invoices = $existingCheck->json('invoices') ?? [];
                if (! empty($invoices[0]['id'])) {
                    $existingInvoice = $invoices[0];
                    $qoyodId = (string) $existingInvoice['id'];

                    $sync->markAsSuccess($qoyodId, [
                        'invoice' => $existingInvoice,
                        'note' => 'تم العثور على الفاتورة مسبقاً في قيود بنفس الرقم المرجعي',
                    ]);

                    return [
                        'success' => true,
                        'qoyod_id' => $qoyodId,
                        'message' => "تمت المزامنة بنجاح (الفاتورة مسجلة مسبقاً في قيود برقم {$qoyodId})",
                    ];
                }
            }

            // 2. Resolve Contact ID (Customer in Qoyod)
            $contactId = $this->resolveContactId($order, $headers);

            // 3. Resolve Inventory ID (Warehouse)
            $inventoryId = $this->resolveInventoryId($headers);

            // 4. Build Line Items
            $lineItems = $this->buildLineItems($order, $headers);

            if (empty($lineItems)) {
                throw new Exception('لا توجد أصناف أو منتجات مسجلة في هذا الطلب لإنشاء الفاتورة');
            }

            // 5. Build Invoice Payload
            $issueDate = $order->order_date ? $order->order_date->format('Y-m-d') : now()->format('Y-m-d');

            $invoiceData = [
                'contact_id' => $contactId,
                'issue_date' => $issueDate,
                'due_date' => $issueDate,
                'reference' => $reference,
                'description' => "فاتورة طلب ترينديول رقم: {$order->order_number} - الشحنة: {$order->shipment_package_id}",
                'status' => 'Approved',
                'line_items' => $lineItems,
            ];

            if (! empty($inventoryId)) {
                $invoiceData['inventory_id'] = (int) $inventoryId;
            }

            $payload = [
                'invoice' => $invoiceData,
            ];

            // 6. Send POST Request to Qoyod
            $timeout = (int) config('services.qoyod.timeout', 30);
            $response = Http::withHeaders($headers)
                ->timeout($timeout)
                ->post("{$this->baseUrl}/invoices", $payload);

            $responseBody = $response->json();

            if ($response->successful()) {
                $invoiceObj = $responseBody['invoice'] ?? $responseBody;
                $qoyodId = (string) ($invoiceObj['id'] ?? $invoiceObj['reference'] ?? 'SUCCESS');

                $sync->update([
                    'request_payload' => $payload,
                ]);
                $sync->markAsSuccess($qoyodId, $responseBody);

                Log::info("Qoyod invoice created successfully for order {$order->order_number}", [
                    'qoyod_id' => $qoyodId,
                    'reference' => $reference,
                ]);

                return [
                    'success' => true,
                    'qoyod_id' => $qoyodId,
                    'message' => "تم إصدار الفاتورة في قيود بنجاح برقم: {$qoyodId}",
                ];
            }

            // Handle API Errors
            $status = $response->status();
            $errorMessage = "فشل في قيود (كود {$status})";

            if (isset($responseBody['errors'])) {
                if (is_array($responseBody['errors'])) {
                    $errorMessage .= ': ' . json_encode($responseBody['errors'], JSON_UNESCAPED_UNICODE);
                } else {
                    $errorMessage .= ': ' . (string) $responseBody['errors'];
                }
            } elseif (isset($responseBody['message'])) {
                $errorMessage .= ': ' . (string) $responseBody['message'];
            } else {
                $errorMessage .= ': ' . substr($response->body(), 0, 300);
            }

            $sync->update([
                'request_payload' => $payload,
            ]);
            $sync->markAsFailed($errorMessage, $responseBody ?: ['raw' => $response->body()]);

            Log::error("Failed to create Qoyod invoice for order {$order->order_number}", [
                'status' => $status,
                'response' => $responseBody,
            ]);

            return [
                'success' => false,
                'error' => $errorMessage,
            ];
        } catch (Throwable $e) {
            $errorMessage = 'استثناء أثناء المزامنة مع قيود: ' . $e->getMessage();
            $sync->markAsFailed($errorMessage);

            Log::error("Exception in QoyodInvoiceSyncService for sync {$sync->id}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => $errorMessage,
            ];
        }
    }

    /**
     * Sync invoice for a TrendyolOrder directly.
     */
    public function syncOrderInvoice(TrendyolOrder $order): array
    {
        $sync = $order->syncs()->firstOrCreate(
            ['type' => QoyodSync::TYPE_INVOICE],
            [
                'status' => QoyodSync::STATUS_PENDING,
                'qoyod_reference' => 'TY-' . ($order->shipment_package_id ?: $order->order_number),
            ]
        );

        return $this->syncInvoice($sync);
    }

    /**
     * Batch process pending invoices.
     */
    public function syncPendingInvoices(int $limit = 50): array
    {
        $pendingSyncs = QoyodSync::where('status', QoyodSync::STATUS_PENDING)
            ->where('type', QoyodSync::TYPE_INVOICE)
            ->take($limit)
            ->get();

        $processed = 0;
        $success = 0;
        $failed = 0;
        $errors = [];

        foreach ($pendingSyncs as $sync) {
            $result = $this->syncInvoice($sync);
            $processed++;

            if ($result['success']) {
                $success++;
            } else {
                $failed++;
                $errors[] = "الطلب {$sync->order?->order_number}: {$result['error']}";
            }

            // Slight delay to avoid hitting Qoyod API rate limit (30 req/min)
            usleep(200000); // 0.2s
        }

        return [
            'processed' => $processed,
            'success' => $success,
            'failed' => $failed,
            'errors' => $errors,
        ];
    }

    /**
     * Resolve Customer ID in Qoyod (Search by phone/email or create new).
     */
    protected function resolveContactId(TrendyolOrder $order, array $headers): int
    {
        $name = $order->customer_full_name ?: ('عميل ترينديول ' . $order->order_number);
        $phone = $order->customer_phone;
        $email = $order->customer_email;

        // 1. Search existing customer by phone
        if (! empty($phone)) {
            $search = Http::withHeaders($headers)
                ->timeout(10)
                ->get("{$this->baseUrl}/customers", [
                    'q[phone_eq]' => $phone,
                ]);

            if ($search->successful() && ! empty($search->json('customers')[0]['id'])) {
                return (int) $search->json('customers')[0]['id'];
            }
        }

        // 2. Search existing customer by email
        if (! empty($email)) {
            $search = Http::withHeaders($headers)
                ->timeout(10)
                ->get("{$this->baseUrl}/customers", [
                    'q[email_eq]' => $email,
                ]);

            if ($search->successful() && ! empty($search->json('customers')[0]['id'])) {
                return (int) $search->json('customers')[0]['id'];
            }
        }

        // 3. Search customer by exact name
        $searchName = Http::withHeaders($headers)
            ->timeout(10)
            ->get("{$this->baseUrl}/customers", [
                'q[name_eq]' => $name,
            ]);

        if ($searchName->successful() && ! empty($searchName->json('customers')[0]['id'])) {
            return (int) $searchName->json('customers')[0]['id'];
        }

        // 4. Create new customer profile in Qoyod
        $customerPayload = [
            'customer' => [
                'name' => $name,
                'phone' => $phone ?: null,
                'email' => $email ?: null,
                'status' => 'Active',
                'vat_number' => $order->tax_number ?: null,
            ],
        ];

        $createResponse = Http::withHeaders($headers)
            ->timeout(10)
            ->post("{$this->baseUrl}/customers", $customerPayload);

        if ($createResponse->successful() && ! empty($createResponse->json('customer')['id'])) {
            return (int) $createResponse->json('customer')['id'];
        }

        // 5. Fallback to first existing customer in Qoyod if creation was rejected
        $allCustomers = Http::withHeaders($headers)
            ->timeout(10)
            ->get("{$this->baseUrl}/customers", ['page' => 1]);

        if ($allCustomers->successful() && ! empty($allCustomers->json('customers')[0]['id'])) {
            return (int) $allCustomers->json('customers')[0]['id'];
        }

        throw new Exception('تعذر العثور على عميل أو إنشاء عميل جديد في قيود: ' . $createResponse->body());
    }

    /**
     * Resolve default or active Inventory ID in Qoyod.
     */
    protected function resolveInventoryId(array $headers): ?int
    {
        if (! empty($this->inventoryId) && is_numeric($this->inventoryId)) {
            return (int) $this->inventoryId;
        }

        // Automatically fetch the first active warehouse from Qoyod
        $response = Http::withHeaders($headers)
            ->timeout(10)
            ->get("{$this->baseUrl}/inventories");

        if ($response->successful()) {
            $inventories = $response->json('inventories') ?? [];
            if (! empty($inventories[0]['id'])) {
                return (int) $inventories[0]['id'];
            }
        }

        return null;
    }

    /**
     * Build invoice line items and resolve/create catalog products.
     */
    protected function buildLineItems(TrendyolOrder $order, array $headers): array
    {
        $lineItems = [];

        foreach ($order->items as $item) {
            $productId = $this->resolveProductId($item, $headers);

            $quantity = max((float) ($item->quantity ?: 1), 1.0);
            $unitPrice = (float) $item->unit_price;
            $discount = (float) ($item->discount ?: 0);
            $vatRate = (float) ($item->vat_rate ?: 15);

            $line = [
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'discount' => $discount,
                'tax_percent' => $vatRate,
            ];

            if ($productId) {
                $line['product_id'] = $productId;
            }

            if (! empty($item->product_name)) {
                $line['description'] = $item->product_name;
            }

            $lineItems[] = $line;
        }

        return $lineItems;
    }

    /**
     * Resolve product ID from Qoyod or automatically create product in Qoyod catalog.
     */
    protected function resolveProductId(TrendyolOrderItem $item, array $headers): ?int
    {
        $sku = $item->merchant_sku ?: ($item->barcode ?: ('TY-' . $item->line_item_id));

        // 1. Search by SKU
        if (! empty($sku)) {
            $search = Http::withHeaders($headers)
                ->timeout(10)
                ->get("{$this->baseUrl}/products", [
                    'q[sku_eq]' => $sku,
                ]);

            if ($search->successful() && ! empty($search->json('products')[0]['id'])) {
                return (int) $search->json('products')[0]['id'];
            }
        }

        // 2. Search by Arabic Product Name
        $name = $item->product_name ?: 'منتج ترينديول';
        $searchName = Http::withHeaders($headers)
            ->timeout(10)
            ->get("{$this->baseUrl}/products", [
                'q[name_ar_eq]' => $name,
            ]);

        if ($searchName->successful() && ! empty($searchName->json('products')[0]['id'])) {
            return (int) $searchName->json('products')[0]['id'];
        }

        // 3. Auto-create product in Qoyod
        $categoryId = config('services.qoyod.default_category_id') ?: 1;
        $productPayload = [
            'product' => [
                'name_ar' => $name,
                'name_en' => $sku ?: $name,
                'sku' => $sku,
                'category_id' => (int) $categoryId,
                'buying_price' => '0.00',
                'selling_price' => (string) number_format((float) $item->unit_price, 2, '.', ''),
                'track_quantity' => false,
            ],
        ];

        $createResponse = Http::withHeaders($headers)
            ->timeout(10)
            ->post("{$this->baseUrl}/products", $productPayload);

        if ($createResponse->successful() && ! empty($createResponse->json('product')['id'])) {
            return (int) $createResponse->json('product')['id'];
        }

        // 4. Fallback to first existing product in Qoyod if available
        $fallback = Http::withHeaders($headers)
            ->timeout(10)
            ->get("{$this->baseUrl}/products", ['page' => 1]);

        if ($fallback->successful() && ! empty($fallback->json('products')[0]['id'])) {
            return (int) $fallback->json('products')[0]['id'];
        }

        return null;
    }
}

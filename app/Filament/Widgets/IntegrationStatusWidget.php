<?php

namespace App\Filament\Widgets;

use App\Models\QoyodSync;
use App\Models\TrendyolOrder;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;
use Throwable;

class IntegrationStatusWidget extends Widget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.integration-status-widget';

    public array $trendyol = [];

    public array $qoyod = [];

    public array $system = [];

    public array $orderStats = [];

    public string $lastCheckedAt = '';

    public function mount(): void
    {
        $this->refreshStatuses();
    }

    public function refreshStatuses(): void
    {
        $this->trendyol = $this->checkTrendyol();
        $this->qoyod = $this->checkQoyod();
        $this->system = $this->checkSystem();
        $this->orderStats = $this->getOrderStats();
        $this->lastCheckedAt = now()->format('H:i:s d/m/Y');
    }

    public function runCheck(): void
    {
        $this->refreshStatuses();

        Notification::make()
            ->title('تم فحص وتحديث حالة الاتصال والإحصائيات بنجاح')
            ->success()
            ->send();
    }

    public function retryFailedSyncs(): void
    {
        $updatedCount = QoyodSync::where('status', QoyodSync::STATUS_FAILED)
            ->update([
                'status' => QoyodSync::STATUS_PENDING,
                'last_error' => null,
            ]);

        $this->refreshStatuses();

        if ($updatedCount > 0) {
            Notification::make()
                ->title("تمت إعادة توجيه {$updatedCount} عملية فاشلة لطابور المزامنة بنجاح")
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('لا توجد عمليات فاشلة لإعادة محاولتها حالياً')
                ->info()
                ->send();
        }
    }

    public function syncTrendyolOrders(): void
    {
        try {
            $syncService = app(\App\Services\TrendyolOrderSyncService::class);
            $result = $syncService->syncOrders();

            $this->refreshStatuses();

            if ($result['success']) {
                Notification::make()
                    ->title('تم سحب ومزامنة طلبات ترينديول بنجاح')
                    ->body("تم جلب وتحديث {$result['total_fetched']} طلب (إضافة {$result['created']} جديد، وتحديث {$result['updated']}).")
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('حدثت مشكلة أثناء المزامنة')
                    ->body(implode("\n", array_slice($result['errors'], 0, 3)))
                    ->warning()
                    ->send();
            }
        } catch (Throwable $e) {
            Notification::make()
                ->title('تعذر الاتصال بـ ترينديول')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getViewData(): array
    {
        return [
            'trendyol' => $this->trendyol,
            'qoyod' => $this->qoyod,
            'system' => $this->system,
            'orderStats' => $this->orderStats,
            'lastCheckedAt' => $this->lastCheckedAt,
        ];
    }

    protected function checkTrendyol(): array
    {
        $baseUrl = config('services.trendyol.base_url', 'https://apigw.trendyol.com');
        $supplierId = config('services.trendyol.supplier_id');
        $apiKey = config('services.trendyol.api_key');
        $apiSecret = config('services.trendyol.api_secret');
        $approvedStatuses = config('services.trendyol.approved_statuses', ['Shipped', 'Delivered']);
        $integrationRef = config('services.trendyol.integration_ref');

        if (empty($apiKey) || empty($apiSecret) || empty($supplierId)) {
            $missing = [];
            if (empty($supplierId)) $missing[] = 'TRENDYOL_SUPPLIER_ID';
            if (empty($apiKey)) $missing[] = 'TRENDYOL_API_KEY';
            if (empty($apiSecret)) $missing[] = 'TRENDYOL_API_SECRET';

            return [
                'connected' => false,
                'status' => 'warning',
                'title' => 'متجر ترينديول (Trendyol)',
                'message' => 'بيانات الاعتماد غير مكتملة في ملف .env',
                'supplierId' => $supplierId ?: 'غير محدد',
                'baseUrl' => $baseUrl,
                'apiKey' => $this->maskSecret($apiKey),
                'approvedStatuses' => implode(', ', $approvedStatuses),
                'integrationRef' => $integrationRef ?: 'غير محدد',
                'totalOrders' => null,
                'latency' => null,
                'httpCode' => null,
                'diagnosis' => 'المتغيرات التالية مفقودة في ملف .env: (' . implode(', ', $missing) . '). يرجى إدخالها أولاً.',
                'actionSteps' => [
                    'أضف معرف البائع ومفاتيح API في ملف .env',
                    'تأكد من عدم وجود مسافات فارغة قبل أو بعد المفاتيح',
                ],
                'rawError' => null,
                'error' => 'الخلل: المتغيرات التالية مفقودة في ملف .env (' . implode(', ', $missing) . ')',
            ];
        }

        $start = microtime(true);
        try {
            $response = Http::withBasicAuth($apiKey, $apiSecret)
                ->withHeaders([
                    'User-Agent' => "{$supplierId} - SelfIntegration",
                    'Accept' => 'application/json',
                ])
                ->timeout(7)
                ->get("{$baseUrl}/integration/order/sellers/{$supplierId}/orders", [
                    'size' => 1,
                ]);

            $latency = round((microtime(true) - $start) * 1000);

            if ($response->successful()) {
                $totalOrders = $response->json('totalElements') ?? 0;
                $totalPages = $response->json('totalPages') ?? 0;

                return [
                    'connected' => true,
                    'status' => 'success',
                    'title' => 'متجر ترينديول (Trendyol)',
                    'message' => 'متصل بنجاح مع خوادم ترينديول وجاهز للمزامنة اللحظية',
                    'supplierId' => $supplierId,
                    'baseUrl' => $baseUrl,
                    'apiKey' => $this->maskSecret($apiKey),
                    'approvedStatuses' => implode(', ', $approvedStatuses),
                    'integrationRef' => $integrationRef ?: 'تكامل ذاتي (SelfIntegration)',
                    'totalOrders' => $totalOrders,
                    'totalPages' => $totalPages,
                    'latency' => "{$latency} ms",
                    'httpCode' => $response->status(),
                    'diagnosis' => null,
                    'actionSteps' => [],
                    'rawError' => null,
                    'error' => null,
                ];
            }

            $httpCode = $response->status();
            $body = $response->json() ?? $response->body();
            $rawError = is_array($body) ? json_encode($body, JSON_UNESCAPED_UNICODE) : (string) $body;

            $diagnosis = "فشل الاتصال بخادم ترينديول (رمز HTTP {$httpCode})";
            $actionSteps = [];

            if ($httpCode === 401) {
                $diagnosis = "خطأ في التحقق والمصادقة (Unauthorized 401): رفض خادم ترينديول مفتاح API Key والسر Secret لهذا البائع ({$supplierId}).";
                $actionSteps = [
                    'ادخل إلى لوحة بائع ترينديول: partner.trendyol.com',
                    'من القائمة العلوية اضغط على اسم المتجر > Hesap Bilgilerim (معلومات الحساب) > Entegrasyon Bilgileri (معلومات التكامل)',
                    'تأكد أن خيار التكامل هو "Kendi Entegrasyonum" (تكامل ذاتي) أو قم بتوليد مفاتيح API جديدة (Yenile)',
                    "تأكد أن رقم البائع (Supplier ID) هو فعلاً {$supplierId}",
                    'انسخ API Key و API Secret بدقة دون مسافات إضافية إلى ملف .env',
                ];
            } elseif ($httpCode === 403) {
                $diagnosis = 'تم رفض الطلب (403 Forbidden): ترينديول حظرت الطلب بسبب User-Agent أو عدم تفعيل الصلاحية للمتجر.';
                $actionSteps = [
                    'تأكد من تفعيل صلاحيات قراءة الطلبات في لوحة ترينديول',
                    'تأكد أن حساب البائع نشط ومفعل لدى ترينديول',
                ];
            } elseif ($httpCode === 429) {
                $diagnosis = 'تجاوز حد الطلبات المسموح (Rate Limit 429): تم إرسال طلبات زائدة إلى ترينديول.';
                $actionSteps = [
                    'انتظر دقيقة واحدة ثم أعد المحاولة، ترينديول تتيح 1000 طلب بالدقيقة',
                ];
            }

            return [
                'connected' => false,
                'status' => 'danger',
                'title' => 'متجر ترينديول (Trendyol)',
                'message' => "تعذر المصادقة والاتصال بـ Trendyol (كود {$httpCode})",
                'supplierId' => $supplierId,
                'baseUrl' => $baseUrl,
                'apiKey' => $this->maskSecret($apiKey),
                'approvedStatuses' => implode(', ', $approvedStatuses),
                'integrationRef' => $integrationRef ?: 'تكامل ذاتي (SelfIntegration)',
                'totalOrders' => null,
                'latency' => "{$latency} ms",
                'httpCode' => $httpCode,
                'diagnosis' => $diagnosis,
                'actionSteps' => $actionSteps,
                'rawError' => $rawError,
                'error' => "Unauthorized {$httpCode}: {$diagnosis} " . ($rawError ?: ''),
            ];
        } catch (Throwable $e) {
            return [
                'connected' => false,
                'status' => 'danger',
                'title' => 'متجر ترينديول (Trendyol)',
                'message' => 'تعذر الاتصال بالشبكة مع خادم ترينديول (Network Timeout / Failure)',
                'supplierId' => $supplierId,
                'baseUrl' => $baseUrl,
                'apiKey' => $this->maskSecret($apiKey),
                'approvedStatuses' => implode(', ', $approvedStatuses),
                'integrationRef' => $integrationRef ?: 'غير محدد',
                'totalOrders' => null,
                'latency' => null,
                'httpCode' => null,
                'diagnosis' => 'انقطع الاتصال أو انتهت مهلة الاستجابة: ' . $e->getMessage(),
                'actionSteps' => [
                    'تحقق من اتصال الإنترنت في السيرفر',
                    'تحقق من قدرة السيرفر على الوصول لعنوان https://apigw.trendyol.com',
                ],
                'rawError' => $e->getMessage(),
                'error' => 'الخلل في الشبكة: ' . $e->getMessage(),
            ];
        }
    }

    protected function checkQoyod(): array
    {
        $baseUrl = config('services.qoyod.base_url', 'https://api.qoyod.com/2.0');
        $apiKey = config('services.qoyod.api_key');
        $inventoryId = config('services.qoyod.default_inventory_id');
        $taxId = config('services.qoyod.default_tax_id', 1);

        if (empty($apiKey)) {
            return [
                'connected' => false,
                'status' => 'warning',
                'title' => 'نظام قيود المحاسبي (Qoyod)',
                'message' => 'مفتاح API-KEY غير محدد في ملف .env',
                'baseUrl' => $baseUrl,
                'apiKey' => 'غير مدخل',
                'inventoryId' => $inventoryId ?: 'غير محدد',
                'taxId' => $taxId,
                'inventoriesCount' => null,
                'latency' => null,
                'httpCode' => null,
                'diagnosis' => 'المتغير QOYOD_API_KEY غير موجود في ملف .env.',
                'actionSteps' => [
                    'ادخل إلى حسابك في قيود على https://app.qoyod.com',
                    'انتقل إلى: الإعدادات > الإعدادات العامة > مفتاح API',
                    'انسخ المفتاح وضعه في ملف .env تحت QOYOD_API_KEY=...',
                    'حدد معرف المستودع التلقائي QOYOD_DEFAULT_INVENTORY_ID',
                ],
                'rawError' => null,
                'error' => 'الخلل: المتغير QOYOD_API_KEY غير موجود في ملف .env.',
            ];
        }

        $start = microtime(true);
        try {
            $response = Http::withHeaders([
                'API-KEY' => $apiKey,
                'Accept' => 'application/json',
            ])
                ->timeout(7)
                ->get("{$baseUrl}/inventories");

            $latency = round((microtime(true) - $start) * 1000);

            if ($response->successful()) {
                $inventories = $response->json('inventories') ?? [];
                $invCount = count($inventories);

                return [
                    'connected' => true,
                    'status' => 'success',
                    'title' => 'نظام قيود المحاسبي (Qoyod)',
                    'message' => 'متصل بنجاح وجاهز لإنشاء الفواتير ومزامنة المستودعات',
                    'baseUrl' => $baseUrl,
                    'apiKey' => $this->maskSecret($apiKey),
                    'inventoryId' => $inventoryId ?: "تلقائي ({$invCount} مستودع متاح)",
                    'taxId' => $taxId,
                    'inventoriesCount' => $invCount,
                    'latency' => "{$latency} ms",
                    'httpCode' => $response->status(),
                    'diagnosis' => null,
                    'actionSteps' => [],
                    'rawError' => null,
                    'error' => null,
                ];
            }

            $httpCode = $response->status();
            $body = $response->json() ?? $response->body();
            $rawError = is_array($body) ? json_encode($body, JSON_UNESCAPED_UNICODE) : (string) $body;

            $diagnosis = "فشل الاتصال بـ قيود (كود استجابة {$httpCode})";
            $actionSteps = [];

            if ($httpCode === 401) {
                $diagnosis = 'مفتاح QOYOD_API_KEY غير صالح أو منتهي الصلاحية أو تم إلغاؤه في قيود.';
                $actionSteps = [
                    'تأكد من نسخ المفتاح الصحيح من إعدادات قيود > مفتاح API',
                    'تأكد من عدم وجود مسافات إضافية في ملف .env',
                ];
            } elseif ($httpCode === 429) {
                $diagnosis = 'تجاوز حد الاستهلاك المسموح به في قيود (30 طلب في الدقيقة).';
                $actionSteps = [
                    'سيتم تقليل وتيرة الطلبات تلقائياً بالخلفية للالتزام بـ 30 req/min',
                ];
            }

            return [
                'connected' => false,
                'status' => 'danger',
                'title' => 'نظام قيود المحاسبي (Qoyod)',
                'message' => "فشل الاتصال بنظام قيود (كود {$httpCode})",
                'baseUrl' => $baseUrl,
                'apiKey' => $this->maskSecret($apiKey),
                'inventoryId' => $inventoryId ?: 'غير محدد',
                'taxId' => $taxId,
                'inventoriesCount' => null,
                'latency' => "{$latency} ms",
                'httpCode' => $httpCode,
                'diagnosis' => $diagnosis,
                'actionSteps' => $actionSteps,
                'rawError' => $rawError,
                'error' => "{$diagnosis} " . ($rawError ?: ''),
            ];
        } catch (Throwable $e) {
            return [
                'connected' => false,
                'status' => 'danger',
                'title' => 'نظام قيود المحاسبي (Qoyod)',
                'message' => 'تعذر الاتصال بخادم قيود (Network Error)',
                'baseUrl' => $baseUrl,
                'apiKey' => $this->maskSecret($apiKey),
                'inventoryId' => $inventoryId ?: 'غير محدد',
                'taxId' => $taxId,
                'inventoriesCount' => null,
                'latency' => null,
                'httpCode' => null,
                'diagnosis' => 'انقطع الاتصال بخادم قيود: ' . $e->getMessage(),
                'actionSteps' => [
                    'تحقق من الوصول إلى الرابط https://api.qoyod.com',
                ],
                'rawError' => $e->getMessage(),
                'error' => 'الخلل في الشبكة أو الاتصال: ' . $e->getMessage(),
            ];
        }
    }

    protected function checkSystem(): array
    {
        $dbConnected = false;
        $dbLatency = null;
        $dbError = null;
        try {
            $start = microtime(true);
            DB::connection()->getPdo();
            $dbLatency = round((microtime(true) - $start) * 1000) . ' ms';
            $dbConnected = true;
        } catch (Throwable $e) {
            $dbError = $e->getMessage();
        }

        $redisConnected = false;
        $redisLatency = null;
        $queueSize = 0;
        $redisError = null;
        try {
            $start = microtime(true);
            Redis::ping();
            $redisLatency = round((microtime(true) - $start) * 1000) . ' ms';
            $redisConnected = true;
            $queueSize = Queue::size();
        } catch (Throwable $e) {
            $redisError = $e->getMessage();
        }

        return [
            'db' => [
                'connected' => $dbConnected,
                'latency' => $dbLatency,
                'database' => config('database.connections.mysql.database'),
                'driver' => config('database.default'),
                'error' => $dbError,
            ],
            'redis' => [
                'connected' => $redisConnected,
                'latency' => $redisLatency,
                'queueSize' => $queueSize,
                'driver' => config('queue.default'),
                'error' => $redisError,
            ],
        ];
    }

    protected function getOrderStats(): array
    {
        $totalOrders = TrendyolOrder::count();
        $syncedInvoices = QoyodSync::where('type', QoyodSync::TYPE_INVOICE)->where('status', QoyodSync::STATUS_SUCCESS)->count();
        $creditNotes = QoyodSync::where('type', QoyodSync::TYPE_CREDIT_NOTE)->where('status', QoyodSync::STATUS_SUCCESS)->count();
        $pending = QoyodSync::where('status', QoyodSync::STATUS_PENDING)->count();
        $failed = QoyodSync::where('status', QoyodSync::STATUS_FAILED)->count();

        $todayOrders = TrendyolOrder::whereDate('created_at', today())->count();
        $todaySynced = QoyodSync::where('status', QoyodSync::STATUS_SUCCESS)->whereDate('synced_at', today())->count();

        $totalGross = (float) TrendyolOrder::sum('gross_amount');
        $totalPrice = (float) TrendyolOrder::sum('total_price');

        // Status breakdown
        $statuses = [
            'Created' => TrendyolOrder::where('status', 'Created')->count(),
            'Picking' => TrendyolOrder::where('status', 'Picking')->count(),
            'Invoiced' => TrendyolOrder::where('status', 'Invoiced')->count(),
            'Shipped' => TrendyolOrder::where('status', 'Shipped')->count(),
            'Delivered' => TrendyolOrder::where('status', 'Delivered')->count(),
            'Cancelled' => TrendyolOrder::where('status', 'Cancelled')->count(),
            'Returned' => TrendyolOrder::where('status', 'Returned')->count(),
        ];

        $completedOps = $syncedInvoices + $creditNotes;
        $totalAttempted = $completedOps + $failed;
        $successRate = $totalAttempted > 0 ? round(($completedOps / $totalAttempted) * 100, 1) : 100;

        return [
            'totalOrders' => $totalOrders,
            'syncedInvoices' => $syncedInvoices,
            'creditNotes' => $creditNotes,
            'pending' => $pending,
            'failed' => $failed,
            'todayOrders' => $todayOrders,
            'todaySynced' => $todaySynced,
            'totalGross' => $totalGross,
            'totalPrice' => $totalPrice,
            'statuses' => $statuses,
            'successRate' => $successRate,
        ];
    }

    protected function maskSecret(?string $value): string
    {
        if (empty($value)) {
            return 'غير مدخل';
        }

        $length = strlen($value);
        if ($length <= 6) {
            return str_repeat('*', $length);
        }

        return substr($value, 0, 2) . str_repeat('*', $length - 4) . substr($value, -2);
    }
}

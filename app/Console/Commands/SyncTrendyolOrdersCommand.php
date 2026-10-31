<?php

namespace App\Console\Commands;

use App\Services\TrendyolOrderSyncService;
use Illuminate\Console\Command;
use Throwable;

class SyncTrendyolOrdersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trendyol:sync-orders
                            {--all : سحب كافة الطلبات القديمة والجديدة بدون حد للصفحات}
                            {--pages= : تحديد الحد الأقصى لعدد الصفحات (افتراضياً صفحة واحدة للمزامنة السريعة)}
                            {--size=50 : عدد الطلبات في الصفحة الواحدة}
                            {--status= : تصفية حسب حالة معينة (مثال: Shipped, Delivered, Created)}
                            {--start-date= : تصفية الطلبات من تاريخ معين (مثال: 2026-10-01)}
                            {--end-date= : تصفية الطلبات إلى تاريخ معين (مثال: 2026-10-31)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'سحب ومزامنة طلبات وشحنات متجر ترينديول (Trendyol) إلى قاعدة البيانات المحلية';

    /**
     * Execute the console command.
     */
    public function handle(TrendyolOrderSyncService $syncService): int
    {
        $force = (bool) $this->option('all') || !empty($this->option('start-date'));
        if (! $force && ! \App\Models\AppSetting::get('auto_sync_enabled', config('services.trendyol.auto_sync_enabled', true))) {
            $this->warn('تم تخطي المزامنة التلقائية: المزامنة التلقائية معطلة حالياً من إعدادات النظام.');
            return Command::SUCCESS;
        }

        $this->info('بدء الاتصال بخوادم ترينديول لسحب الطلبات...');

        $syncAll = (bool) $this->option('all');
        $pagesOption = $this->option('pages');
        $maxPages = $syncAll ? null : ($pagesOption !== null ? (int) $pagesOption : 1);
        $pageSize = (int) $this->option('size') ?: 50;
        $status = $this->option('status');
        $startDate = $this->option('start-date');
        $endDate = $this->option('end-date');

        $this->line("الوضع: " . ($syncAll ? 'سحب كامل الطلبات (القديمة والجديدة)' : "سحب محدود ({$maxPages} صفحات)"));
        if (!empty($status)) {
            $this->line("التصفية حسب الحالة: {$status}");
        }
        if (!empty($startDate) || !empty($endDate)) {
            $this->line("النطاق الزمني: من [{$startDate}] إلى [{$endDate}]");
        }

        try {
            $result = $syncService->syncOrders(
                maxPages: $maxPages,
                pageSize: $pageSize,
                status: $status,
                startDate: $startDate,
                endDate: $endDate
            );

            $this->newLine();
            $this->info('--- نتائج عملية المزامنة ---');
            $this->line("إجمالي الطلبات المستلمة: {$result['total_fetched']}");
            $this->line("الطلبات الجديدة المضافة: {$result['created']}");
            $this->line("الطلبات المحدثة: {$result['updated']}");
            $this->line("عدد الصفحات التي تمت معالجتها: {$result['pages_processed']}");

            if (!empty($result['errors'])) {
                $this->newLine();
                $this->warn('حدثت بعض التحذيرات/الأخطاء أثناء المزامنة:');
                foreach ($result['errors'] as $error) {
                    $this->error("- {$error}");
                }
            }

            $this->info('اكتملت المزامنة بنجاح.');
            return Command::SUCCESS;
        } catch (Throwable $e) {
            $this->error('فشلت عملية المزامنة: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

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
                            {--status= : تصفية حسب حالة معينة (مثال: Shipped, Delivered, Created)}';

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
        $this->info('بدء الاتصال بخوادم ترينديول لسحب الطلبات...');

        $syncAll = (bool) $this->option('all');
        $pagesOption = $this->option('pages');
        $maxPages = $syncAll ? null : ($pagesOption !== null ? (int) $pagesOption : 1);
        $pageSize = (int) $this->option('size') ?: 50;
        $status = $this->option('status');

        $this->line("الوضع: " . ($syncAll ? 'سحب كامل الطلبات (القديمة والجديدة)' : "سحب محدود ({$maxPages} صفحات)"));
        if (!empty($status)) {
            $this->line("التصفية حسب الحالة: {$status}");
        }

        try {
            $result = $syncService->syncOrders(
                maxPages: $maxPages,
                pageSize: $pageSize,
                status: $status
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

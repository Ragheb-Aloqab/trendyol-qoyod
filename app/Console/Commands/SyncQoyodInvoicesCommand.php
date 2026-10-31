<?php

namespace App\Console\Commands;

use App\Services\QoyodInvoiceSyncService;
use Illuminate\Console\Command;
use Throwable;

class SyncQoyodInvoicesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qoyod:sync-invoices
                            {--limit=50 : الحد الأقصى لعدد الفواتير المعلقة المراد معالجتها}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'مزامنة وإرسال فواتير قيود المعلقة (Pending) إلى نظام قيود المحاسبي عبر الـ API';

    /**
     * Execute the console command.
     */
    public function handle(QoyodInvoiceSyncService $qoyodService): int
    {
        $limit = (int) $this->option('limit') ?: 50;
        $this->info("بدء معالجة فواتير قيود المعلقة (الحد الأقصى: {$limit})...");

        try {
            $result = $qoyodService->syncPendingInvoices($limit);

            $this->newLine();
            $this->info('--- نتائج مزامنة فواتير قيود ---');
            $this->line("إجمالي الفواتير المعالجة: {$result['processed']}");
            $this->line("تمت المزامنة بنجاح: {$result['success']}");
            $this->line("فشلت المزامنة: {$result['failed']}");

            if (! empty($result['errors'])) {
                $this->newLine();
                $this->warn('قائمة الأخطاء:');
                foreach ($result['errors'] as $error) {
                    $this->error("- {$error}");
                }
            }

            $this->info('اكتملت العملية.');
            return Command::SUCCESS;
        } catch (Throwable $e) {
            $this->error('حدث خطأ أثناء معالجة فواتير قيود: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

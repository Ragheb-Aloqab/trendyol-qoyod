<?php

namespace App\Jobs;

use App\Models\TrendyolOrder;
use App\Models\QoyodSync;
use App\Services\QoyodInvoiceSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class FetchQoyodInvoiceDetailsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string|int $qoyodId,
        public ?int $trendyolOrderId = null,
        public ?int $qoyodSyncId = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(QoyodInvoiceSyncService $syncService): void
    {
        Log::info("Executing FetchQoyodInvoiceDetailsJob for Qoyod ID: {$this->qoyodId}");

        $order = $this->trendyolOrderId ? TrendyolOrder::find($this->trendyolOrderId) : null;
        $sync = $this->qoyodSyncId ? QoyodSync::find($this->qoyodSyncId) : null;

        try {
            $syncService->fetchAndStoreInvoiceDetails($this->qoyodId, $order, $sync);
        } catch (Throwable $e) {
            Log::error("FetchQoyodInvoiceDetailsJob failed for Qoyod ID: {$this->qoyodId}", [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}

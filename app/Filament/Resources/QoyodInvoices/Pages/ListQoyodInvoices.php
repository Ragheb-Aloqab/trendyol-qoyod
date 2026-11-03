<?php

namespace App\Filament\Resources\QoyodInvoices\Pages;

use App\Filament\Resources\QoyodInvoices\QoyodInvoiceResource;
use App\Models\QoyodInvoice;
use App\Models\QoyodSync;
use App\Services\QoyodInvoiceSyncService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

class ListQoyodInvoices extends ListRecords
{
    protected static string $resource = QoyodInvoiceResource::class;

    public function getMaxContentWidth(): Width|string|null
    {
        return Width::Full;
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return 'فواتير قيود المسترجعة';
    }

    public function getSubheading(): ?string
    {
        return 'عرض ومتابعة فواتير المبيعات الصادرة في قيود لطلبات ترينديول، وتفاصيل الضرائب والخصومات، مع إمكانية تحميل وطباعة ملفات الـ PDF مباشرة.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('retry_failed_fetches')
                ->label('إعادة جلب الفواتير المتعثرة')
                ->icon('heroicon-m-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('إعادة جلب تفاصيل الفواتير المتعثرة')
                ->modalDescription('سيتم الاتصال بقيود لجلب بيانات وملفات PDF للفواتير التي فشل جلبها سابقاً دون إنشاء فواتير جديدة.')
                ->modalSubmitActionLabel('بدء الجلب')
                ->modalCancelActionLabel('إلغاء')
                ->action(function () {
                    $qoyodService = app(QoyodInvoiceSyncService::class);
                    $failedInvoices = QoyodInvoice::where('fetch_status', QoyodInvoice::FETCH_STATUS_FAILED)->take(30)->get();

                    $successCount = 0;
                    $failCount = 0;

                    foreach ($failedInvoices as $invoice) {
                        $res = $qoyodService->fetchAndStoreInvoiceDetails($invoice->qoyod_invoice_id, $invoice->order, $invoice->sync);
                        if ($res && $res->fetch_status === QoyodInvoice::FETCH_STATUS_SUCCESS) {
                            $successCount++;
                        } else {
                            $failCount++;
                        }
                    }

                    Notification::make()
                        ->title('اكتملت محاولة جلب الفواتير')
                        ->body("تم تحديث {$successCount} فاتورة بنجاح. فشل: {$failCount}")
                        ->info()
                        ->send();
                }),
        ];
    }
}

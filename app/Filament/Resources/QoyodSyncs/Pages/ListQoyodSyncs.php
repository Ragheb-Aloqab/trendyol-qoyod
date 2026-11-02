<?php

namespace App\Filament\Resources\QoyodSyncs\Pages;

use App\Filament\Resources\QoyodSyncs\QoyodSyncResource;
use App\Services\QoyodInvoiceSyncService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

class ListQoyodSyncs extends ListRecords
{
    protected static string $resource = QoyodSyncResource::class;

    public function getMaxContentWidth(): Width|string|null
    {
        return Width::Full;
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return 'سجلات المزامنة مع قيود';
    }

    public function getSubheading(): ?string
    {
        return 'سجل الفواتير والإشعارات المرسلة إلى نظام قيود وحالات المزامنة مع خيارات إعادة الإرسال والمعالجة الفورية.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sync_all_pending')
                ->label('مزامنة كافة المعلقات الآن')
                ->icon('heroicon-m-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('مزامنة الفواتير المعلقة دفعة واحدة')
                ->modalDescription('سيتم إرسال كافة فواتير قيود المعلقة (قيد الانتظار) إلى حسابك في قيود فوراً.')
                ->modalSubmitActionLabel('بدء المزامنة')
                ->modalCancelActionLabel('إلغاء')
                ->action(function () {
                    $qoyodService = app(QoyodInvoiceSyncService::class);
                    $result = $qoyodService->syncPendingInvoices(50);

                    Notification::make()
                        ->title('اكتملت مزامنة الفواتير المعلقة')
                        ->body("تمت معالجة {$result['processed']} فاتورة (نجاح: {$result['success']} - فشل: {$result['failed']})")
                        ->info()
                        ->send();
                }),
        ];
    }
}

<?php

namespace App\Filament\Resources\TrendyolOrders\Pages;

use App\Filament\Resources\TrendyolOrders\TrendyolOrderResource;
use App\Models\QoyodSync;
use App\Models\TrendyolOrder;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewTrendyolOrder extends ViewRecord
{
    protected static string $resource = TrendyolOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('retry_sync')
                ->label(fn (TrendyolOrder $record): string => $record->invoiceSync?->status === QoyodSync::STATUS_SUCCESS ? 'إعادة الإرسال لقيود' : 'إرسال لقيود الآن')
                ->icon('heroicon-o-paper-airplane')
                ->color(fn (TrendyolOrder $record): string => $record->invoiceSync?->status === QoyodSync::STATUS_SUCCESS ? 'gray' : 'primary')
                ->requiresConfirmation()
                ->modalHeading('إرسال الفاتورة إلى قيود الآن')
                ->modalDescription(fn (TrendyolOrder $record): string => "سيتم إنشاء وإرسال فاتورة المبيعات للطلب رقم {$record->order_number} مباشرة إلى حساب قيود.")
                ->modalSubmitActionLabel('نعم، أرسل الآن')
                ->modalCancelActionLabel('إلغاء')
                ->action(function (TrendyolOrder $record) {
                    $qoyodService = app(\App\Services\QoyodInvoiceSyncService::class);
                    $result = $qoyodService->syncOrderInvoice($record);

                    if ($result['success']) {
                        Notification::make()
                            ->title('تم إرسال الفاتورة إلى قيود بنجاح')
                            ->body($result['message'] ?? 'تمت المزامنة بنجاح')
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('فشل إرسال الفاتورة لقيود')
                            ->body($result['error'] ?? 'حدث خطأ غير معروف')
                            ->danger()
                            ->persistent()
                            ->send();
                    }
                }),
        ];
    }
}

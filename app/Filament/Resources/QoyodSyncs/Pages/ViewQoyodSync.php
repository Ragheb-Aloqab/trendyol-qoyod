<?php

namespace App\Filament\Resources\QoyodSyncs\Pages;

use App\Filament\Resources\QoyodSyncs\QoyodSyncResource;
use App\Models\QoyodSync;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewQoyodSync extends ViewRecord
{
    protected static string $resource = QoyodSyncResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('retry')
                ->label(fn (QoyodSync $record): string => $record->status === QoyodSync::STATUS_FAILED ? 'إعادة المحاولة' : 'إرسال لقيود الآن')
                ->icon(fn (QoyodSync $record): string => $record->status === QoyodSync::STATUS_FAILED ? 'heroicon-o-arrow-path' : 'heroicon-o-paper-airplane')
                ->color(fn (QoyodSync $record): string => $record->status === QoyodSync::STATUS_FAILED ? 'warning' : 'success')
                ->visible(fn (QoyodSync $record): bool => $record->status !== QoyodSync::STATUS_SUCCESS)
                ->requiresConfirmation()
                ->modalHeading(fn (QoyodSync $record): string => $record->status === QoyodSync::STATUS_FAILED ? 'تأكيد إعادة المحاولة وإرسال الفاتورة' : 'تأكيد إرسال الفاتورة لقيود الآن')
                ->modalDescription('سيتم إرسال بيانات الفاتورة والأصناف والعميل إلى حسابك في قيود فوراً.')
                ->modalSubmitActionLabel('نعم، أرسل لقيود الآن')
                ->modalCancelActionLabel('إلغاء')
                ->action(function (QoyodSync $record) {
                    $qoyodService = app(\App\Services\QoyodInvoiceSyncService::class);
                    $result = $qoyodService->syncInvoice($record);

                    if ($result['success']) {
                        Notification::make()
                            ->title('تمت المزامنة مع قيود بنجاح')
                            ->body($result['message'] ?? 'تم إرسال الفاتورة بنجاح')
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('فشلت المزامنة مع قيود')
                            ->body($result['error'] ?? 'حدث خطأ أثناء إرسال الفاتورة لقيود')
                            ->danger()
                            ->persistent()
                            ->send();
                    }
                }),
        ];
    }
}

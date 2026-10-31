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
                ->label('مزامنة مع قيود')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('تأكيد المزامنة مع قيود')
                ->modalDescription('هل أنت متأكد من رغبتك في إرسال فاتورة هذا الطلب إلى نظام قيود؟')
                ->modalSubmitActionLabel('نعم، ابدأ المزامنة')
                ->modalCancelActionLabel('إلغاء')
                ->action(function (TrendyolOrder $record) {
                    $sync = $record->syncs()->firstOrCreate(
                        ['type' => QoyodSync::TYPE_INVOICE],
                        [
                            'status' => QoyodSync::STATUS_PENDING,
                            'qoyod_reference' => 'TY-' . $record->shipment_package_id,
                        ]
                    );
                    $sync->update(['status' => QoyodSync::STATUS_PENDING]);

                    Notification::make()
                        ->title('تمت جدولة الطلب للمزامنة مع قيود بنجاح')
                        ->success()
                        ->send();
                }),
        ];
    }
}

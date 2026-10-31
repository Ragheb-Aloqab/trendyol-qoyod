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
                ->label('إعادة المحاولة')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('تأكيد إعادة المحاولة')
                ->modalDescription('هل ترغب في إعادة جدولة هذه المعاملة للمزامنة مجدداً؟')
                ->modalSubmitActionLabel('نعم، أعد المحاولة')
                ->modalCancelActionLabel('إلغاء')
                ->action(function (QoyodSync $record) {
                    $record->update([
                        'status' => QoyodSync::STATUS_PENDING,
                    ]);

                    Notification::make()
                        ->title('تمت إعادة جدولة العملية بنجاح')
                        ->success()
                        ->send();
                }),
        ];
    }
}

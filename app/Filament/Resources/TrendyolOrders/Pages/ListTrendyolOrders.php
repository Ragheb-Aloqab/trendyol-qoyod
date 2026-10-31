<?php

namespace App\Filament\Resources\TrendyolOrders\Pages;

use App\Filament\Resources\TrendyolOrders\TrendyolOrderResource;
use App\Services\TrendyolOrderSyncService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Throwable;

class ListTrendyolOrders extends ListRecords
{
    protected static string $resource = TrendyolOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sync_all_orders')
                ->label('سحب كافة الطلبات من ترينديول')
                ->icon('heroicon-m-arrow-path-rounded-square')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('سحب كافة طلبات ترينديول (القديمة والجديدة)')
                ->modalDescription('سيتم الاتصال بخوادم ترينديول وسحب جميع الطلبات المسجلة في حساب المتجر وتخزينها في قاعدة البيانات المحلية. هل ترغب بالبدء الآن؟')
                ->modalSubmitActionLabel('نعم، ابدأ سحب كافة الطلبات')
                ->modalCancelActionLabel('إلغاء')
                ->action(function (TrendyolOrderSyncService $syncService) {
                    try {
                        $result = $syncService->syncOrders(maxPages: null, pageSize: 50);

                        if ($result['success']) {
                            Notification::make()
                                ->title('تمت مزامنة وسحب طلبات ترينديول بنجاح')
                                ->body("تم جلب وتحديث {$result['total_fetched']} طلب (إضافة {$result['created']} جديد، وتحديث {$result['updated']}).")
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('حدثت مشكلة أثناء السحب')
                                ->body(implode("\n", array_slice($result['errors'], 0, 3)))
                                ->warning()
                                ->send();
                        }
                    } catch (Throwable $e) {
                        Notification::make()
                            ->title('تعذر الاتصال بخوادم ترينديول')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('sync_recent_orders')
                ->label('مزامنة سريعة (أحدث الطلبات)')
                ->icon('heroicon-m-arrow-path')
                ->color('gray')
                ->action(function (TrendyolOrderSyncService $syncService) {
                    try {
                        $result = $syncService->syncOrders(maxPages: 1, pageSize: 50);

                        if ($result['success']) {
                            Notification::make()
                                ->title('تم سحب أحدث الطلبات بنجاح')
                                ->body("تم جلب {$result['total_fetched']} طلب (إضافة {$result['created']} جديد، وتحديث {$result['updated']}).")
                                ->success()
                                ->send();
                        }
                    } catch (Throwable $e) {
                        Notification::make()
                            ->title('فشل سحب أحدث الطلبات')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}


<?php

namespace App\Filament\Resources\TrendyolOrders\Pages;

use App\Filament\Resources\TrendyolOrders\TrendyolOrderResource;
use App\Services\TrendyolOrderSyncService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Throwable;

class ListTrendyolOrders extends ListRecords
{
    protected static string $resource = TrendyolOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sync_by_date_range')
                ->label('مزامنة حسب الفترة (من تاريخ إلى تاريخ)')
                ->icon('heroicon-m-calendar-days')
                ->color('warning')
                ->modalHeading('سحب ومزامنة طلبات ترينديول حسب فترة محددة')
                ->modalDescription('حدد تاريخ البداية وتاريخ النهاية لسحب الطلبات المسجلة في متجرك على ترينديول خلال هذه الفترة:')
                ->modalSubmitActionLabel('بدء المزامنة')
                ->modalCancelActionLabel('إلغاء')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            DatePicker::make('start_date')
                                ->label('من تاريخ (بداية الفترة)')
                                ->default(now()->subDays(14)->format('Y-m-d'))
                                ->required()
                                ->native(false)
                                ->maxDate(now())
                                ->prefixIcon('heroicon-m-calendar'),

                            DatePicker::make('end_date')
                                ->label('إلى تاريخ (نهاية الفترة)')
                                ->default(now()->format('Y-m-d'))
                                ->required()
                                ->native(false)
                                ->afterOrEqual('start_date')
                                ->maxDate(now())
                                ->prefixIcon('heroicon-m-calendar'),
                        ]),

                    Select::make('status')
                        ->label('تصفية حسب حالة الطلب (اختياري)')
                        ->placeholder('كافة الحالات')
                        ->options([
                            'Created' => 'طلب جديد (Created)',
                            'Picking' => 'جاري التجهيز (Picking)',
                            'Invoiced' => 'تمت الفوترة (Invoiced)',
                            'Shipped' => 'تم الشحن (Shipped)',
                            'Delivered' => 'تم التوصيل (Delivered)',
                            'Cancelled' => 'ملغي (Cancelled)',
                            'Returned' => 'مرتجع (Returned)',
                            'UnDelivered' => 'تعذر التسليم (UnDelivered)',
                        ]),

                    Select::make('max_pages')
                        ->label('الحد الأقصى لعدد الصفحات')
                        ->options([
                            'all' => 'سحب كافة الصفحات المتاحة في هذه الفترة (شامل)',
                            '1' => 'صفحة واحدة فقط (حتى 50 طلب)',
                            '3' => 'حتى 3 صفحات (150 طلب)',
                            '5' => 'حتى 5 صفحات (250 طلب)',
                            '10' => 'حتى 10 صفحات (500 طلب)',
                        ])
                        ->default('all')
                        ->required(),
                ])
                ->action(function (array $data, TrendyolOrderSyncService $syncService) {
                    try {
                        $maxPages = $data['max_pages'] === 'all' ? null : (int) $data['max_pages'];
                        $status = !empty($data['status']) ? $data['status'] : null;

                        $result = $syncService->syncOrders(
                            maxPages: $maxPages,
                            pageSize: 50,
                            status: $status,
                            startDate: $data['start_date'],
                            endDate: $data['end_date'],
                        );

                        if ($result['success']) {
                            Notification::make()
                                ->title('تمت المزامنة للفترة المحددة بنجاح')
                                ->body("الفترة: من {$data['start_date']} إلى {$data['end_date']}\nإجمالي الطلبات: {$result['total_fetched']} (جديد: {$result['created']}، محدث: {$result['updated']})")
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
                            ->title('تعذر إتمام المزامنة للفترة المحددة')
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
                        } else {
                            Notification::make()
                                ->title('حدثت مشكلة أثناء السحب')
                                ->body(implode("\n", array_slice($result['errors'], 0, 3)))
                                ->warning()
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

            Action::make('sync_all_orders')
                ->label('سحب كافة الطلبات')
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
        ];
    }
}

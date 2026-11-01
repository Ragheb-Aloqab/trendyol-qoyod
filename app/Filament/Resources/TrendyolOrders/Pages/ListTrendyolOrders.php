<?php

namespace App\Filament\Resources\TrendyolOrders\Pages;

use App\Filament\Resources\TrendyolOrders\TrendyolOrderResource;
use App\Services\TrendyolOrderSyncService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Grid;
use Throwable;

class ListTrendyolOrders extends ListRecords
{
    protected static string $resource = TrendyolOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sync_recent_orders')
                ->label('تحديث وجلب الطلبات الجديدة')
                ->icon('heroicon-m-arrow-path')
                ->color('success')
                ->action(function (TrendyolOrderSyncService $syncService) {
                    try {
                        $result = $syncService->syncOrders(maxPages: 5, pageSize: 50);

                        if ($result['success']) {
                            Notification::make()
                                ->title('تم تحديث وجلب الطلبات بنجاح')
                                ->body("تم الاتصال بترينديول: تم إضافة {$result['created']} طلب جديد، وتحديث {$result['updated']} طلب موجود (إجمالي المستلم: {$result['total_fetched']}).")
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
                            ->title('فشل تحديث أحدث الطلبات')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

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
                                ->default(fn () => \App\Models\AppSetting::get('sync_start_date', '2026-10-01'))
                                ->minDate(fn () => \App\Models\AppSetting::get('sync_start_date', '2026-10-01'))
                                ->maxDate(now())
                                ->required()
                                ->native(false)
                                ->helperText('ممنوع اختيار تاريخ يسبق تاريخ بدء المزامنة (2026-10-01).')
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
                            'Delivered' => 'تم التوصيل (Delivered)',
                            'Shipped' => 'تم الشحن (Shipped)',
                            'Picking' => 'جاري التجهيز (Picking)',
                            'Invoiced' => 'تمت الفوترة (Invoiced)',
                            'Created' => 'طلب جديد (Created)',
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

                        $minAllowed = \App\Models\AppSetting::get('sync_start_date', '2026-10-01');
                        $effectiveStart = max($data['start_date'], $minAllowed);

                        $result = $syncService->syncOrders(
                            maxPages: $maxPages,
                            pageSize: 50,
                            status: $status,
                            startDate: $effectiveStart,
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

<?php

namespace App\Filament\Resources\TrendyolOrders\Tables;

use App\Models\QoyodSync;
use App\Models\TrendyolOrder;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class TrendyolOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('order_date', 'desc')
            ->columns([
                TextColumn::make('order_number')
                    ->label('رقم الطلب')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('shipment_package_id')
                    ->label('رقم الشحنة')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('customer_full_name')
                    ->label('اسم العميل')
                    ->searchable(['customer_first_name', 'customer_last_name']),

                TextColumn::make('status')
                    ->label('حالة الطلب في ترينديول')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Delivered' => 'success',
                        'Shipped' => 'info',
                        'Picking', 'Invoiced', 'Created', 'AtCollectionPoint' => 'warning',
                        default => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'Delivered' => 'تم التوصيل',
                        'Shipped' => 'تم الشحن',
                        'Picking' => 'جاري التجهيز',
                        'Invoiced' => 'تمت الفوترة',
                        'Created' => 'طلب جديد',
                        'Cancelled' => 'ملغي',
                        'Returned' => 'مرتجع',
                        'UnDelivered' => 'تعذر التسليم',
                        'AtCollectionPoint' => 'في نقطة الاستلام',
                        'UnSupplied' => 'غير متوفر',
                        default => $state,
                    })
                    ->sortable(),

                TextColumn::make('total_price')
                    ->label('الإجمالي')
                    ->money(fn (TrendyolOrder $record): string => $record->currency ?? 'TRY')
                    ->sortable(),

                TextColumn::make('invoiceSync.status')
                    ->label('حالة المزامنة في قيود')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'success' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'success' => 'تمت المزامنة',
                        'pending' => 'قيد الانتظار',
                        'failed' => 'فشلت',
                        default => 'غير مزامن',
                    }),

                TextColumn::make('order_date')
                    ->label('تاريخ الطلب')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('qoyod_sync_status')
                    ->label('حالة المزامنة في قيود')
                    ->options([
                        'success' => 'تمت المزامنة',
                        'pending' => 'قيد الانتظار',
                        'failed' => 'فشلت المزامنة',
                        'not_synced' => 'غير مزامن',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['value'] ?? null)) {
                            return $query;
                        }

                        return match ($data['value']) {
                            'not_synced' => $query->whereDoesntHave('invoiceSync'),
                            default => $query->whereHas('invoiceSync', fn (Builder $q) => $q->where('status', $data['value'])),
                        };
                    }),

                SelectFilter::make('status')
                    ->label('تصفية حسب حالة الطلب في ترينديول')
                    ->options([
                        'Created' => 'طلب جديد',
                        'Picking' => 'جاري التجهيز',
                        'Invoiced' => 'تمت الفوترة',
                        'Shipped' => 'تم الشحن',
                        'Delivered' => 'تم التوصيل',
                        'Cancelled' => 'ملغي',
                        'Returned' => 'مرتجع',
                        'UnDelivered' => 'تعذر التسليم',
                    ]),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('عرض التفاصيل'),

                Action::make('sync_to_qoyod')
                    ->label(fn (TrendyolOrder $record): string => $record->invoiceSync?->status === QoyodSync::STATUS_SUCCESS ? 'إعادة الإرسال لقيود' : 'إرسال لقيود الآن')
                    ->icon('heroicon-o-paper-airplane')
                    ->color(fn (TrendyolOrder $record): string => $record->invoiceSync?->status === QoyodSync::STATUS_SUCCESS ? 'gray' : 'primary')
                    ->requiresConfirmation()
                    ->modalHeading(fn (TrendyolOrder $record): string => $record->invoiceSync?->status === QoyodSync::STATUS_SUCCESS ? 'إعادة إرسال الفاتورة لقيود وإنشاء فاتورة جديدة' : 'إرسال الفاتورة إلى قيود الآن')
                    ->modalDescription(fn (TrendyolOrder $record): string => "سيتم إنشاء وإرسال فاتورة المبيعات للطلب رقم {$record->order_number} مباشرة إلى حساب قيود بالبيانات والأسعار المحدثة.")
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
            ])
            ->toolbarActions([
                Action::make('sync_new_trendyol_orders')
                    ->label('تحديث وجلب الطلبات الجديدة')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->action(function () {
                        $syncService = app(\App\Services\TrendyolOrderSyncService::class);
                        try {
                            $result = $syncService->syncOrders(maxPages: 5, pageSize: 50);

                            if ($result['success']) {
                                Notification::make()
                                    ->title('اكتمل تحديث الطلبات من ترينديول')
                                    ->body("تم فحص ترينديول بنجاح: تم إضافة {$result['created']} طلب جديد، وتحديث {$result['updated']} طلب موجود (إجمالي المستلم: {$result['total_fetched']}).")
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('حدثت مشكلة أثناء جلب الطلبات')
                                    ->body(implode("\n", array_slice($result['errors'], 0, 3)))
                                    ->warning()
                                    ->send();
                            }
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('فشل تحديث الطلبات من ترينديول')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                BulkActionGroup::make([
                    BulkAction::make('bulk_sync_to_qoyod')
                        ->label('إعادة إرسال الطلبات المحددة لقيود')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->modalHeading('إعادة مزامنة الطلبات المحددة مع قيود')
                        ->modalDescription('سيتم إنشاء وإرسال فواتير جديدة إلى قيود لكافة الطلبات المحددة بالبيانات والأسعار المحدثة.')
                        ->modalSubmitActionLabel('نعم، أرسل الآن')
                        ->modalCancelActionLabel('إلغاء')
                        ->action(function (Collection $records) {
                            $qoyodService = app(\App\Services\QoyodInvoiceSyncService::class);
                            $successCount = 0;
                            $failCount = 0;
                            $errors = [];

                            foreach ($records as $record) {
                                $result = $qoyodService->syncOrderInvoice($record);
                                if ($result['success']) {
                                    $successCount++;
                                } else {
                                    $failCount++;
                                    $errors[] = "الطلب {$record->order_number}: " . ($result['error'] ?? 'خطأ');
                                }
                            }

                            if ($failCount === 0) {
                                Notification::make()
                                    ->title('اكتملت المزامنة بنجاح')
                                    ->body("تم إرسال {$successCount} فاتورة إلى قيود بنجاح.")
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title("اكتملت المزامنة: {$successCount} ناجحة، {$failCount} فاشلة")
                                    ->body(implode("\n", array_slice($errors, 0, 3)))
                                    ->warning()
                                    ->persistent()
                                    ->send();
                            }
                        }),
                ]),

                Action::make('resync_all_synced_invoices')
                    ->label('إعادة مزامنة كافة الفواتير المحذوفة من قيود')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('إعادة مزامنة كافة الفواتير التي تمت مزامنتها مسبقاً')
                    ->modalDescription('سيتم فحص كافة الطلبات المسجلة كـ "تمت المزامنة" وإنشاء فواتير مبيعات جديدة لها في قيود بالبيانات والأسعار والضرائب المحدثة. هل تريد المتابعة؟')
                    ->modalSubmitActionLabel('نعم، ابدأ إعادة المزامنة')
                    ->modalCancelActionLabel('إلغاء')
                    ->action(function () {
                        $qoyodService = app(\App\Services\QoyodInvoiceSyncService::class);
                        $result = $qoyodService->resyncAllSyncedInvoices(100);

                        if ($result['processed'] === 0) {
                            Notification::make()
                                ->title('لا توجد فواتير')
                                ->body('لم يتم العثور على أي طلبات مسجلة كـ "تمت المزامنة".')
                                ->info()
                                ->send();
                            return;
                        }

                        if ($result['failed'] === 0) {
                            Notification::make()
                                ->title('اكتملت إعادة المزامنة بنجاح')
                                ->body("تم إنشاء وإرسال {$result['success']} فاتورة جديدة إلى قيود بنجاح.")
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title("اكتملت العملية: {$result['success']} ناجحة، {$result['failed']} فاشلة")
                                ->body(implode("\n", array_slice($result['errors'], 0, 3)))
                                ->warning()
                                ->persistent()
                                ->send();
                        }
                    }),
            ]);
    }
}

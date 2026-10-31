<?php

namespace App\Filament\Resources\TrendyolOrders\Tables;

use App\Models\QoyodSync;
use App\Models\TrendyolOrder;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

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
                SelectFilter::make('status')
                    ->label('تصفية حسب حالة الطلب')
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
            ])
            ->toolbarActions([
                // Read-only
            ]);
    }
}

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
            ])
            ->toolbarActions([
                // Read-only
            ]);
    }
}

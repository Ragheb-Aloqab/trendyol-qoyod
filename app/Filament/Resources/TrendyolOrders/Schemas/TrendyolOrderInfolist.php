<?php

namespace App\Filament\Resources\TrendyolOrders\Schemas;

use App\Models\TrendyolOrder;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TrendyolOrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات الطلب الأساسية')
                    ->columns(4)
                    ->components([
                        TextEntry::make('order_number')
                            ->label('رقم الطلب')
                            ->copyable()
                            ->weight('bold'),

                        TextEntry::make('shipment_package_id')
                            ->label('رقم الشحنة / الطرد')
                            ->copyable(),

                        TextEntry::make('status')
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
                                default => $state,
                            }),

                        TextEntry::make('order_date')
                            ->label('تاريخ ووقت الطلب')
                            ->dateTime('Y-m-d H:i'),

                        TextEntry::make('gross_amount')
                            ->label('المبلغ الإجمالي')
                            ->money(fn (TrendyolOrder $record): string => $record->currency ?? 'TRY'),

                        TextEntry::make('total_discount')
                            ->label('إجمالي الخصم')
                            ->money(fn (TrendyolOrder $record): string => $record->currency ?? 'TRY'),

                        TextEntry::make('total_price')
                            ->label('الصافي المطلوب')
                            ->money(fn (TrendyolOrder $record): string => $record->currency ?? 'TRY')
                            ->weight('bold'),

                        TextEntry::make('currency')
                            ->label('العملة'),
                    ]),

                Section::make('بيانات العميل والفواتير')
                    ->columns(3)
                    ->components([
                        TextEntry::make('customer_full_name')
                            ->label('اسم العميل'),

                        TextEntry::make('customer_email')
                            ->label('البريد الإلكتروني')
                            ->copyable(),

                        TextEntry::make('customer_phone')
                            ->label('رقم الهاتف'),

                        TextEntry::make('tax_number')
                            ->label('الرقم الضريبي')
                            ->placeholder('غير متوفر (أفراد)'),

                        TextEntry::make('tax_office')
                            ->label('الفرع أو المكتب الضريبي')
                            ->placeholder('غير متوفر'),

                        TextEntry::make('is_commercial')
                            ->label('نوع الشراء')
                            ->formatStateUsing(fn (bool $state): string => $state ? 'مؤسسي / شركة' : 'فردي'),
                    ]),

                Section::make('حالة المزامنة مع قيود')
                    ->columns(3)
                    ->components([
                        TextEntry::make('invoiceSync.status')
                            ->label('حالة الفاتورة')
                            ->badge()
                            ->color(fn (?string $state): string => match ($state) {
                                'success' => 'success',
                                'pending' => 'warning',
                                'failed' => 'danger',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (?string $state): string => match ($state) {
                                'success' => 'تمت المزامنة بنجاح',
                                'pending' => 'قيد الانتظار',
                                'failed' => 'فشلت المزامنة',
                                default => 'لم تتم المزامنة بعد',
                            }),

                        TextEntry::make('invoiceSync.qoyod_id')
                            ->label('رقم الفاتورة في قيود')
                            ->copyable()
                            ->placeholder('لا يوجد'),

                        TextEntry::make('invoiceSync.qoyod_reference')
                            ->label('الرقم المرجعي في قيود')
                            ->copyable()
                            ->placeholder('لا يوجد'),

                        TextEntry::make('invoiceSync.synced_at')
                            ->label('تاريخ المزامنة')
                            ->dateTime('Y-m-d H:i')
                            ->placeholder('لم تتم بعد'),

                        TextEntry::make('invoiceSync.attempts')
                            ->label('عدد المحاولات')
                            ->placeholder('0'),

                        TextEntry::make('invoiceSync.last_error')
                            ->label('رسالة الخطأ الأخير')
                            ->columnSpanFull()
                            ->color('danger')
                            ->placeholder('لا توجد أخطاء مسجلة'),
                    ]),

                Section::make('المنتجات وبنود الطلب')
                    ->components([
                        RepeatableEntry::make('items')
                            ->label('')
                            ->columns(6)
                            ->components([
                                TextEntry::make('product_name')
                                    ->label('اسم المنتج')
                                    ->columnSpan(2)
                                    ->weight('bold'),

                                TextEntry::make('merchant_sku')
                                    ->label('رمز الصنف (SKU)')
                                    ->placeholder('-'),

                                TextEntry::make('barcode')
                                    ->label('الباركود')
                                    ->placeholder('-'),

                                TextEntry::make('quantity')
                                    ->label('الكمية'),

                                TextEntry::make('unit_price')
                                    ->label('سعر الوحدة')
                                    ->money(fn ($record): string => $record->currency ?? 'TRY'),
                            ]),
                    ]),
            ]);
    }
}

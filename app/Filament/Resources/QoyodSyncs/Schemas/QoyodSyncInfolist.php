<?php

namespace App\Filament\Resources\QoyodSyncs\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class QoyodSyncInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('تفاصيل العملية')
                    ->columns(3)
                    ->components([
                        TextEntry::make('order.order_number')
                            ->label('رقم الطلب')
                            ->copyable()
                            ->weight('bold'),

                        TextEntry::make('order.shipment_package_id')
                            ->label('رقم الشحنة')
                            ->copyable(),

                        TextEntry::make('type')
                            ->label('نوع المستند')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'invoice' => 'info',
                                'credit_note' => 'warning',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'invoice' => 'فاتورة مبيعات',
                                'credit_note' => 'إشعار دائن (مرتجع)',
                                default => $state,
                            }),

                        TextEntry::make('status')
                            ->label('حالة المزامنة')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'success' => 'success',
                                'pending' => 'warning',
                                'failed' => 'danger',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'success' => 'ناجح',
                                'pending' => 'قيد الانتظار',
                                'failed' => 'فشل',
                                default => $state,
                            }),

                        TextEntry::make('qoyod_id')
                            ->label('معرف قيود')
                            ->copyable()
                            ->placeholder('لا يوجد'),

                        TextEntry::make('qoyod_reference')
                            ->label('الرقم المرجعي في قيود')
                            ->copyable()
                            ->placeholder('لا يوجد'),

                        TextEntry::make('attempts')
                            ->label('عدد المحاولات'),

                        TextEntry::make('synced_at')
                            ->label('تاريخ المزامنة')
                            ->dateTime('Y-m-d H:i:s')
                            ->placeholder('لم تتم بعد'),

                        TextEntry::make('created_at')
                            ->label('تاريخ الإنشاء')
                            ->dateTime('Y-m-d H:i:s'),
                    ]),

                Section::make('تفاصيل الخطأ إن وجد')
                    ->collapsible()
                    ->components([
                        TextEntry::make('last_error')
                            ->label('')
                            ->color('danger')
                            ->columnSpanFull()
                            ->placeholder('لا توجد أخطاء مسجلة، العملية ناجحة.'),
                    ]),

                Section::make('البيانات البرمجية (Payloads)')
                    ->collapsible()
                    ->columns(2)
                    ->components([
                        TextEntry::make('request_payload')
                            ->label('البيانات المرسلة إلى قيود (Request)')
                            ->formatStateUsing(fn ($state): string => $state ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : 'لا توجد بيانات')
                            ->codeBlock('json'),

                        TextEntry::make('response_payload')
                            ->label('الاستجابة المستلمة من قيود (Response)')
                            ->formatStateUsing(fn ($state): string => $state ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : 'لا توجد بيانات')
                            ->codeBlock('json'),
                    ]),
            ]);
    }
}

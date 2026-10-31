<?php

namespace App\Filament\Resources\QoyodSyncs\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

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
                            ->weight('bold')
                            ->placeholder('غير متوفر'),

                        TextEntry::make('order.shipment_package_id')
                            ->label('رقم الشحنة')
                            ->copyable()
                            ->placeholder('غير متوفر'),

                        TextEntry::make('type')
                            ->label('نوع المستند')
                            ->badge()
                            ->color(fn (?string $state): string => match ($state) {
                                'invoice' => 'info',
                                'credit_note' => 'warning',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (?string $state): string => match ($state) {
                                'invoice' => 'فاتورة مبيعات',
                                'credit_note' => 'إشعار دائن (مرتجع)',
                                default => $state ?? 'غير محدد',
                            }),

                        TextEntry::make('status')
                            ->label('حالة المزامنة')
                            ->badge()
                            ->color(fn (?string $state): string => match ($state) {
                                'success' => 'success',
                                'pending' => 'warning',
                                'failed' => 'danger',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (?string $state): string => match ($state) {
                                'success' => 'ناجح',
                                'pending' => 'قيد الانتظار',
                                'failed' => 'فشل',
                                default => $state ?? 'غير محدد',
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
                            ->label('عدد المحاولات')
                            ->placeholder('0'),

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
                            ->wrap()
                            ->placeholder('لا توجد أخطاء مسجلة، العملية ناجحة.'),
                    ]),

                Section::make('البيانات البرمجية (Payloads)')
                    ->collapsible()
                    ->columns(1)
                    ->components([
                        TextEntry::make('request_payload')
                            ->label('البيانات المرسلة إلى قيود (Request)')
                            ->columnSpanFull()
                            ->copyable()
                            ->formatStateUsing(function ($state): ?HtmlString {
                                if (blank($state)) {
                                    return new HtmlString('<span class="text-sm text-gray-500">لا توجد بيانات</span>');
                                }

                                $json = is_string($state) ? $state : json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

                                return new HtmlString('<pre dir="ltr" class="p-3 text-xs font-mono rounded-lg overflow-x-auto bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 border border-gray-200 dark:border-gray-800 max-h-96">' . e($json) . '</pre>');
                            })
                            ->html(),

                        TextEntry::make('response_payload')
                            ->label('الاستجابة المستلمة من قيود (Response)')
                            ->columnSpanFull()
                            ->copyable()
                            ->formatStateUsing(function ($state): ?HtmlString {
                                if (blank($state)) {
                                    return new HtmlString('<span class="text-sm text-gray-500">لا توجد بيانات</span>');
                                }

                                $json = is_string($state) ? $state : json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

                                return new HtmlString('<pre dir="ltr" class="p-3 text-xs font-mono rounded-lg overflow-x-auto bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 border border-gray-200 dark:border-gray-800 max-h-96">' . e($json) . '</pre>');
                            })
                            ->html(),
                    ]),
            ]);
    }
}

<?php

namespace App\Filament\Resources\QoyodInvoices\Schemas;

use App\Models\QoyodInvoice;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class QoyodInvoiceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات الفاتورة والطلب')
                    ->columns(4)
                    ->components([
                        TextEntry::make('order.order_number')
                            ->label('رقم طلب ترينديول')
                            ->copyable()
                            ->weight('bold')
                            ->placeholder('غير محدد'),

                        TextEntry::make('qoyod_invoice_id')
                            ->label('معرف الفاتورة في قيود')
                            ->badge()
                            ->color('info')
                            ->copyable(),

                        TextEntry::make('qoyod_invoice_number')
                            ->label('الرقم المرجعي في قيود')
                            ->copyable()
                            ->placeholder('غير محدد'),

                        TextEntry::make('status')
                            ->label('حالة الفاتورة')
                            ->badge()
                            ->color(fn (?string $state): string => match (strtolower((string) $state)) {
                                'approved' => 'success',
                                'paid' => 'success',
                                'draft' => 'gray',
                                default => 'info',
                            })
                            ->formatStateUsing(fn (?string $state): string => match (strtolower((string) $state)) {
                                'approved' => 'معتمدة (Approved)',
                                'paid' => 'مدفوعة (Paid)',
                                'draft' => 'مسودة (Draft)',
                                default => $state ?? '-',
                            }),

                        TextEntry::make('issue_date')
                            ->label('تاريخ الإصدار')
                            ->date('Y-m-d')
                            ->placeholder('-'),

                        TextEntry::make('due_date')
                            ->label('تاريخ الاستحقاق')
                            ->date('Y-m-d')
                            ->placeholder('-'),

                        TextEntry::make('fetch_status')
                            ->label('حالة جلب الفاتورة')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                QoyodInvoice::FETCH_STATUS_SUCCESS => 'success',
                                QoyodInvoice::FETCH_STATUS_PENDING => 'warning',
                                QoyodInvoice::FETCH_STATUS_FAILED => 'danger',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                QoyodInvoice::FETCH_STATUS_SUCCESS => 'تم جلب التفاصيل بنجاح',
                                QoyodInvoice::FETCH_STATUS_PENDING => 'قيد الانتظار',
                                QoyodInvoice::FETCH_STATUS_FAILED => 'فشل جلب التفاصيل',
                                default => $state,
                            }),

                        TextEntry::make('fetched_at')
                            ->label('تاريخ ووقت الجلب')
                            ->dateTime('Y-m-d H:i:s')
                            ->placeholder('-'),
                    ]),

                Section::make('المبالغ والبيانات المالية للفاتورة')
                    ->columns(4)
                    ->components([
                        TextEntry::make('subtotal')
                            ->label('المبلغ قبل الضريبة (Subtotal)')
                            ->formatStateUsing(fn ($state): string => number_format((float) $state, 2) . ' ر.س')
                            ->weight('medium'),

                        TextEntry::make('total_discount')
                            ->label('إجمالي الخصم (Discount)')
                            ->formatStateUsing(fn ($state): string => number_format((float) $state, 2) . ' ر.س')
                            ->color('warning')
                            ->weight('medium'),

                        TextEntry::make('tax_amount')
                            ->label('قيمة ضريبة القيمة المضافة (15% VAT)')
                            ->formatStateUsing(fn ($state): string => number_format((float) $state, 2) . ' ر.س')
                            ->color('info')
                            ->weight('medium'),

                        TextEntry::make('total')
                            ->label('الإجمالي النهائي بعد الضريبة (Total)')
                            ->formatStateUsing(fn ($state): string => number_format((float) $state, 2) . ' ر.س')
                            ->weight('bold')
                            ->color('success')
                            ->size('lg'),
                    ]),

                Section::make('مستند الفاتورة الإلكتروني (PDF)')
                    ->columns(1)
                    ->components([
                        TextEntry::make('pdf_url')
                            ->label('رابط ملف الفاتورة المباشر')
                            ->formatStateUsing(function (?string $state): HtmlString {
                                if (empty($state)) {
                                    return new HtmlString('<div class="text-sm text-gray-500">لا يتوفر رابط PDF مخزن حالياً. يمكنك استخدام زر "عرض وتحميل ملف PDF" بالأعلى لجلب الرابط فورياً من قيود.</div>');
                                }

                                return new HtmlString(
                                    '<div class="flex items-center gap-3 py-2">' .
                                    '<a href="' . e($state) . '" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg shadow transition">' .
                                    '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>' .
                                    'فتح وتحميل ملف الفاتورة PDF' .
                                    '</a>' .
                                    '<span class="text-xs text-gray-400 select-all">' . e($state) . '</span>' .
                                    '</div>'
                                );
                            })
                            ->html(),
                    ]),

                Section::make('أصناف الفاتورة المسجلة في قيود')
                    ->collapsible()
                    ->components([
                        TextEntry::make('raw_data.line_items')
                            ->label('')
                            ->formatStateUsing(function ($state): HtmlString {
                                if (empty($state) || !is_array($state)) {
                                    return new HtmlString('<span class="text-sm text-gray-500">لا توجد تفاصيل أصناف مخزنة.</span>');
                                }

                                $html = '<div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-800">' .
                                    '<table class="w-full text-right text-xs divide-y divide-gray-200 dark:divide-gray-800">' .
                                    '<thead class="bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 font-semibold">' .
                                    '<tr>' .
                                    '<th class="px-4 py-3">#</th>' .
                                    '<th class="px-4 py-3">الصنف / الوصف</th>' .
                                    '<th class="px-4 py-3 text-center">الكمية</th>' .
                                    '<th class="px-4 py-3">سعر الوحدة (قبل الضريبة)</th>' .
                                    '<th class="px-4 py-3">قيمة الخصم</th>' .
                                    '<th class="px-4 py-3">نسبة الضريبة</th>' .
                                    '<th class="px-4 py-3">قيمة الضريبة</th>' .
                                    '<th class="px-4 py-3 font-bold">الإجمالي</th>' .
                                    '</tr>' .
                                    '</thead>' .
                                    '<tbody class="divide-y divide-gray-100 dark:divide-gray-800">';

                                foreach ($state as $idx => $item) {
                                    $num = $idx + 1;
                                    $name = e($item['product_name'] ?? $item['description'] ?? 'صنف #' . ($item['product_id'] ?? $num));
                                    $qty = number_format((float) ($item['quantity'] ?? 1), 0);
                                    $unitPrice = number_format((float) ($item['unit_price'] ?? 0), 2) . ' ر.س';
                                    $disc = number_format((float) ($item['discount_amount'] ?? 0), 2) . ' ر.س';
                                    $taxPct = ((float) ($item['tax_percent'] ?? 15)) . '%';
                                    $vatVal = number_format((float) ($item['unrounded_vat_value'] ?? $item['tax_amount'] ?? 0), 2) . ' ر.س';
                                    $lineTotal = number_format((float) ($item['line_total'] ?? 0), 2) . ' ر.س';

                                    $html .= '<tr class="hover:bg-gray-50 dark:hover:bg-gray-900/50">' .
                                        "<td class=\"px-4 py-2.5 text-gray-500\">{$num}</td>" .
                                        "<td class=\"px-4 py-2.5 font-medium text-gray-900 dark:text-gray-100\">{$name}</td>" .
                                        "<td class=\"px-4 py-2.5 text-center\">{$qty}</td>" .
                                        "<td class=\"px-4 py-2.5\">{$unitPrice}</td>" .
                                        "<td class=\"px-4 py-2.5 text-amber-600\">{$disc}</td>" .
                                        "<td class=\"px-4 py-2.5 text-gray-500\">{$taxPct}</td>" .
                                        "<td class=\"px-4 py-2.5 text-blue-600\">{$vatVal}</td>" .
                                        "<td class=\"px-4 py-2.5 font-bold text-emerald-600\">{$lineTotal}</td>" .
                                        '</tr>';
                                }

                                $html .= '</tbody></table></div>';
                                return new HtmlString($html);
                            })
                            ->html(),
                    ]),

                Section::make('تفاصيل الخطأ في حال تعثر الجلب')
                    ->collapsible()
                    ->collapsed(fn (QoyodInvoice $record): bool => empty($record->fetch_error))
                    ->components([
                        TextEntry::make('fetch_error')
                            ->label('')
                            ->color('danger')
                            ->wrap()
                            ->placeholder('لا توجد أخطاء مسجلة، تم جلب تفاصيل الفاتورة بنجاح.'),
                    ]),

                Section::make('الاستجابة البرمجية الخام من قيود (Raw JSON)')
                    ->collapsible()
                    ->collapsed()
                    ->components([
                        TextEntry::make('raw_data')
                            ->label('')
                            ->formatStateUsing(function ($state): ?HtmlString {
                                if (blank($state)) {
                                    return new HtmlString('<span class="text-sm text-gray-500">لا توجد بيانات خام مسجلة</span>');
                                }
                                $json = is_string($state) ? $state : json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                                return new HtmlString('<pre dir="ltr" class="p-3 text-xs font-mono rounded-lg overflow-x-auto bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 border border-gray-200 dark:border-gray-800 max-h-96">' . e($json) . '</pre>');
                            })
                            ->html(),
                    ]),
            ]);
    }
}

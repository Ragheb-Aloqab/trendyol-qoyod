<?php

namespace App\Filament\Resources\QoyodInvoices\Tables;

use App\Models\QoyodInvoice;
use App\Services\QoyodInvoiceSyncService;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class QoyodInvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('order.order_number')
                    ->label('رقم طلب ترينديول')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold')
                    ->placeholder('-'),

                TextColumn::make('qoyod_invoice_id')
                    ->label('معرف قيود')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('qoyod_invoice_number')
                    ->label('الرقم المرجعي')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->placeholder('-'),

                TextColumn::make('issue_date')
                    ->label('تاريخ الإصدار')
                    ->date('Y-m-d')
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('subtotal')
                    ->label('قبل الضريبة')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 2) . ' ر.س')
                    ->alignEnd()
                    ->sortable(),

                TextColumn::make('total_discount')
                    ->label('الخصم')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 2) . ' ر.س')
                    ->alignEnd()
                    ->color('warning')
                    ->sortable(),

                TextColumn::make('tax_amount')
                    ->label('الضريبة (15%)')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 2) . ' ر.س')
                    ->alignEnd()
                    ->sortable(),

                TextColumn::make('total')
                    ->label('الإجمالي النهائي')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 2) . ' ر.س')
                    ->alignEnd()
                    ->weight('bold')
                    ->color('success')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('حالة الفاتورة')
                    ->badge()
                    ->color(fn (?string $state): string => match (strtolower((string) $state)) {
                        'approved' => 'success',
                        'paid' => 'success',
                        'draft' => 'gray',
                        default => 'info',
                    })
                    ->formatStateUsing(fn (?string $state): string => match (strtolower((string) $state)) {
                        'approved' => 'معتمدة',
                        'paid' => 'مدفوعة',
                        'draft' => 'مسودة',
                        default => $state ?? '-',
                    })
                    ->sortable(),

                TextColumn::make('fetch_status')
                    ->label('حالة الجلب')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        QoyodInvoice::FETCH_STATUS_SUCCESS => 'success',
                        QoyodInvoice::FETCH_STATUS_PENDING => 'warning',
                        QoyodInvoice::FETCH_STATUS_FAILED => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        QoyodInvoice::FETCH_STATUS_SUCCESS => 'تم الجلب',
                        QoyodInvoice::FETCH_STATUS_PENDING => 'قيد الانتظار',
                        QoyodInvoice::FETCH_STATUS_FAILED => 'فشل الجلب',
                        default => $state,
                    })
                    ->sortable(),

                TextColumn::make('fetched_at')
                    ->label('تاريخ الجلب')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('fetch_status')
                    ->label('تصفية حسب حالة الجلب')
                    ->options([
                        QoyodInvoice::FETCH_STATUS_SUCCESS => 'تم الجلب بنجاح',
                        QoyodInvoice::FETCH_STATUS_FAILED => 'فشل الجلب',
                        QoyodInvoice::FETCH_STATUS_PENDING => 'قيد الانتظار',
                    ]),

                SelectFilter::make('status')
                    ->label('تصفية حسب حالة الفاتورة في قيود')
                    ->options([
                        'Approved' => 'معتمدة (Approved)',
                        'Draft' => 'مسودة (Draft)',
                    ]),
            ])
            ->recordActions([
                ViewAction::make()
                    ->iconButton()
                    ->tooltip('عرض تفاصيل الفاتورة المسترجعة'),

                Action::make('download_pdf')
                    ->iconButton()
                    ->icon('heroicon-o-arrow-down-tray')
                    ->tooltip('تحميل أو عرض ملف PDF')
                    ->color('success')
                    ->url(function (QoyodInvoice $record) {
                        if (!empty($record->pdf_url)) {
                            return $record->pdf_url;
                        }
                        $service = app(QoyodInvoiceSyncService::class);
                        $freshUrl = $service->fetchInvoicePdfUrl($record->qoyod_invoice_id);
                        if ($freshUrl) {
                            $record->update(['pdf_url' => $freshUrl]);
                            return $freshUrl;
                        }
                        return null;
                    }, shouldOpenInNewTab: true),

                Action::make('retry_fetch')
                    ->iconButton()
                    ->icon('heroicon-o-arrow-path')
                    ->tooltip('إعادة جلب تفاصيل الفاتورة من قيود')
                    ->color(fn (QoyodInvoice $record): string => $record->fetch_status === QoyodInvoice::FETCH_STATUS_FAILED ? 'warning' : 'gray')
                    ->requiresConfirmation()
                    ->modalHeading('إعادة جلب بيانات الفاتورة من قيود')
                    ->modalDescription('سيتم الاتصال بقيود بالمعرف الأصلي لجلب أحدث تفاصيل المبالغ ورابط PDF وتحديث السجل المحلي.')
                    ->modalSubmitActionLabel('جلب وتحديث')
                    ->modalCancelActionLabel('إلغاء')
                    ->action(function (QoyodInvoice $record) {
                        $service = app(QoyodInvoiceSyncService::class);
                        $res = $service->fetchAndStoreInvoiceDetails($record->qoyod_invoice_id, $record->order, $record->sync);

                        if ($res && $res->fetch_status === QoyodInvoice::FETCH_STATUS_SUCCESS) {
                            Notification::make()
                                ->title('تم جلب تفاصيل الفاتورة بنجاح')
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('فشل جلب تفاصيل الفاتورة')
                                ->body($res?->fetch_error ?? 'خطأ غير معروف')
                                ->danger()
                                ->persistent()
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('bulk_retry_fetch')
                        ->label('إعادة جلب تفاصيل الفواتير المحددة')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('تأكيد إعادة جلب الفواتير المحددة من قيود')
                        ->modalDescription('سيتم الاستعلام عن كل فاتورة محددة وتحديث بياناتها المالية وملف الـ PDF دون إنشاء أي فواتير جديدة.')
                        ->modalSubmitActionLabel('بدء الجلب')
                        ->modalCancelActionLabel('إلغاء')
                        ->action(function (Collection $records) {
                            $service = app(QoyodInvoiceSyncService::class);
                            $successCount = 0;
                            $failCount = 0;

                            foreach ($records as $record) {
                                $res = $service->fetchAndStoreInvoiceDetails($record->qoyod_invoice_id, $record->order, $record->sync);
                                if ($res && $res->fetch_status === QoyodInvoice::FETCH_STATUS_SUCCESS) {
                                    $successCount++;
                                } else {
                                    $failCount++;
                                }
                            }

                            Notification::make()
                                ->title('اكتملت عملية الجلب')
                                ->body("تم جلب {$successCount} بنجاح. فشل: {$failCount}")
                                ->info()
                                ->send();
                        }),
                ]),
            ]);
    }
}

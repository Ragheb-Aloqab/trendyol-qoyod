<?php

namespace App\Filament\Resources\QoyodSyncs\Tables;

use App\Models\QoyodSync;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class QoyodSyncsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('order.order_number')
                    ->label('رقم الطلب')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('order.shipment_package_id')
                    ->label('رقم الشحنة')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('type')
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
                    })
                    ->sortable(),

                TextColumn::make('status')
                    ->label('الحالة')
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
                    })
                    ->sortable(),

                TextColumn::make('qoyod_id')
                    ->label('معرف قيود')
                    ->copyable()
                    ->placeholder('-'),

                TextColumn::make('qoyod_reference')
                    ->label('الرقم المرجعي')
                    ->copyable()
                    ->placeholder('-'),

                TextColumn::make('attempts')
                    ->label('المحاولات')
                    ->sortable(),

                TextColumn::make('last_error')
                    ->label('آخر خطأ')
                    ->limit(35)
                    ->tooltip(fn (?string $state): ?string => $state)
                    ->color('danger')
                    ->placeholder('-'),

                TextColumn::make('synced_at')
                    ->label('تاريخ المزامنة')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->placeholder('لم تتم'),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('تصفية حسب نوع المستند')
                    ->options([
                        'invoice' => 'فاتورة مبيعات',
                        'credit_note' => 'إشعار دائن (مرتجع)',
                    ]),
                SelectFilter::make('status')
                    ->label('تصفية حسب الحالة')
                    ->options([
                        'pending' => 'قيد الانتظار',
                        'success' => 'ناجح',
                        'failed' => 'فشل',
                    ]),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('عرض التفاصيل'),

                Action::make('retry')
                    ->label(fn (QoyodSync $record): string => $record->status === QoyodSync::STATUS_FAILED ? 'إعادة المحاولة' : 'إرسال لقيود الآن')
                    ->icon(fn (QoyodSync $record): string => $record->status === QoyodSync::STATUS_FAILED ? 'heroicon-o-arrow-path' : 'heroicon-o-paper-airplane')
                    ->color(fn (QoyodSync $record): string => $record->status === QoyodSync::STATUS_FAILED ? 'warning' : 'success')
                    ->visible(fn (QoyodSync $record): bool => $record->status !== QoyodSync::STATUS_SUCCESS)
                    ->requiresConfirmation()
                    ->modalHeading(fn (QoyodSync $record): string => $record->status === QoyodSync::STATUS_FAILED ? 'تأكيد إعادة المحاولة وإرسال الفاتورة' : 'تأكيد إرسال الفاتورة لقيود الآن')
                    ->modalDescription('سيتم إرسال بيانات الفاتورة والأصناف والعميل إلى حسابك في قيود فوراً.')
                    ->modalSubmitActionLabel('نعم، أرسل لقيود الآن')
                    ->modalCancelActionLabel('إلغاء')
                    ->action(function (QoyodSync $record) {
                        $qoyodService = app(\App\Services\QoyodInvoiceSyncService::class);
                        $result = $qoyodService->syncInvoice($record);

                        if ($result['success']) {
                            Notification::make()
                                ->title('تمت المزامنة مع قيود بنجاح')
                                ->body($result['message'] ?? 'تم إرسال الفاتورة بنجاح')
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('فشلت المزامنة مع قيود')
                                ->body($result['error'] ?? 'حدث خطأ أثناء إرسال الفاتورة لقيود')
                                ->danger()
                                ->persistent()
                                ->send();
                        }
                    }),
            ])
            ->toolbarActions([
                Action::make('sync_all_pending')
                    ->label('مزامنة كافة المعلقات الآن')
                    ->icon('heroicon-o-arrow-path')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->modalHeading('مزامنة الفواتير المعلقة دفعة واحدة')
                    ->modalDescription('سيتم إرسال كافة فواتير قيود المعلقة (قيد الانتظار) إلى حسابك في قيود فوراً.')
                    ->modalSubmitActionLabel('بدء المزامنة')
                    ->modalCancelActionLabel('إلغاء')
                    ->action(function () {
                        $qoyodService = app(\App\Services\QoyodInvoiceSyncService::class);
                        $result = $qoyodService->syncPendingInvoices(50);

                        Notification::make()
                            ->title('اكتملت مزامنة الفواتير المعلقة')
                            ->body("تمت معالجة {$result['processed']} فاتورة (نجاح: {$result['success']} - فشل: {$result['failed']})")
                            ->info()
                            ->send();
                    }),
            ]);
    }
}

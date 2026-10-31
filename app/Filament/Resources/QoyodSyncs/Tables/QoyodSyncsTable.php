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
                    ->label('إعادة المحاولة')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn (QoyodSync $record): bool => $record->status === QoyodSync::STATUS_FAILED || $record->status === QoyodSync::STATUS_PENDING)
                    ->requiresConfirmation()
                    ->modalHeading('تأكيد إعادة المحاولة')
                    ->modalDescription('هل ترغب في إعادة جدولة هذه المعاملة للمزامنة مجدداً؟')
                    ->modalSubmitActionLabel('نعم، أعد المحاولة')
                    ->modalCancelActionLabel('إلغاء')
                    ->action(function (QoyodSync $record) {
                        $record->update(['status' => QoyodSync::STATUS_PENDING]);

                        Notification::make()
                            ->title('تمت إعادة جدولة العملية بنجاح')
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                // Read-only
            ]);
    }
}

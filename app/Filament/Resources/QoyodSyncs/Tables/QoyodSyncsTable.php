<?php

namespace App\Filament\Resources\QoyodSyncs\Tables;

use App\Models\QoyodSync;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

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
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

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
                    ->label('المرجع في قيود')
                    ->copyable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('attempts')
                    ->label('المحاولات')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('last_error')
                    ->label('آخر خطأ')
                    ->limit(25)
                    ->tooltip(fn (?string $state): ?string => $state)
                    ->color('danger')
                    ->placeholder('-'),

                TextColumn::make('synced_at')
                    ->label('تاريخ المزامنة')
                    ->date('Y-m-d')
                    ->description(fn (QoyodSync $record): ?string => $record->synced_at?->format('H:i'))
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
                    ->iconButton()
                    ->tooltip('عرض تفاصيل السجل'),

                Action::make('retry')
                    ->iconButton()
                    ->tooltip(fn (QoyodSync $record): string => match ($record->status) {
                        QoyodSync::STATUS_SUCCESS => 'إعادة الإرسال لقيود',
                        QoyodSync::STATUS_FAILED => 'إعادة المحاولة',
                        default => 'إرسال لقيود الآن',
                    })
                    ->icon(fn (QoyodSync $record): string => match ($record->status) {
                        QoyodSync::STATUS_SUCCESS => 'heroicon-o-paper-airplane',
                        QoyodSync::STATUS_FAILED => 'heroicon-o-arrow-path',
                        default => 'heroicon-o-paper-airplane',
                    })
                    ->color(fn (QoyodSync $record): string => match ($record->status) {
                        QoyodSync::STATUS_SUCCESS => 'gray',
                        QoyodSync::STATUS_FAILED => 'warning',
                        default => 'primary',
                    })
                    ->requiresConfirmation()
                    ->modalHeading(fn (QoyodSync $record): string => match ($record->status) {
                        QoyodSync::STATUS_SUCCESS => 'تأكيد إعادة إرسال الفاتورة لقيود وإنشاء فاتورة جديدة',
                        QoyodSync::STATUS_FAILED => 'تأكيد إعادة المحاولة وإرسال الفاتورة',
                        default => 'تأكيد إرسال الفاتورة لقيود الآن',
                    })
                    ->modalDescription('سيتم التحقق من قيود وإنشاء الفاتورة فوراً بالبيانات المحدثة.')
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
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('bulk_retry_to_qoyod')
                        ->label('إعادة إرسال السجلات المحددة لقيود')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->modalHeading('إعادة مزامنة السجلات المحددة مع قيود')
                        ->modalDescription('سيتم إرسال وإنشاء فواتير جديدة في قيود للسجلات المحددة بالبيانات والأسعار المحدثة.')
                        ->modalSubmitActionLabel('نعم، أرسل الآن')
                        ->modalCancelActionLabel('إلغاء')
                        ->action(function (Collection $records) {
                            $qoyodService = app(\App\Services\QoyodInvoiceSyncService::class);
                            $successCount = 0;
                            $failCount = 0;
                            $errors = [];

                            foreach ($records as $record) {
                                $result = $qoyodService->syncInvoice($record);
                                if ($result['success']) {
                                    $successCount++;
                                } else {
                                    $failCount++;
                                    $errors[] = ($record->order ? "الطلب {$record->order->order_number}: " : '') . ($result['error'] ?? 'خطأ');
                                }
                            }

                            if ($failCount === 0) {
                                Notification::make()
                                    ->title('اكتملت المزامنة بنجاح')
                                    ->body("تم إرسال {$successCount} مستند إلى قيود بنجاح.")
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
            ]);
    }
}

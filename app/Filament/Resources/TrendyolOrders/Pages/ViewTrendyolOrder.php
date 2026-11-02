<?php

namespace App\Filament\Resources\TrendyolOrders\Pages;

use App\Filament\Resources\TrendyolOrders\TrendyolOrderResource;
use App\Models\QoyodSync;
use App\Models\TrendyolOrder;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\Support\Htmlable;

class ViewTrendyolOrder extends ViewRecord
{
    protected static string $resource = TrendyolOrderResource::class;

    public function getMaxContentWidth(): Width|string|null
    {
        return Width::Full;
    }

    public function getTitle(): string|Htmlable
    {
        return "تفاصيل طلب ترينديول رقم #{$this->record->order_number}";
    }

    public function getSubheading(): ?string
    {
        return "تاريخ الطلب: " . ($this->record->order_date ? $this->record->order_date->format('Y-m-d H:i') : '-') . " | العميل: {$this->record->customer_full_name} | إجمالي المبلغ: {$this->record->gross_amount} {$this->record->currency}";
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('retry_sync')
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
        ];
    }
}

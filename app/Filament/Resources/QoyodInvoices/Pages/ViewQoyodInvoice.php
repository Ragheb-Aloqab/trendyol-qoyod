<?php

namespace App\Filament\Resources\QoyodInvoices\Pages;

use App\Filament\Resources\QoyodInvoices\QoyodInvoiceResource;
use App\Models\QoyodInvoice;
use App\Services\QoyodInvoiceSyncService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\Support\Htmlable;

class ViewQoyodInvoice extends ViewRecord
{
    protected static string $resource = QoyodInvoiceResource::class;

    public function getMaxContentWidth(): Width|string|null
    {
        return Width::Full;
    }

    public function getTitle(): string|Htmlable
    {
        $id = $this->record->qoyod_invoice_id ?: $this->record->id;
        return "تفاصيل فاتورة قيود #{$id}";
    }

    public function getSubheading(): ?string
    {
        $orderNum = $this->record->order?->order_number ?? 'غير محدد';
        $ref = $this->record->qoyod_invoice_number ?? 'غير محدد';
        return "طلب ترينديول: #{$orderNum} | الرقم المرجعي في قيود: {$ref}";
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('open_pdf')
                ->label('عرض وتحميل ملف PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->visible(fn (): bool => !empty($this->record->pdf_url))
                ->url(fn (): ?string => $this->record->pdf_url, shouldOpenInNewTab: true),

            Action::make('fetch_pdf')
                ->label('جلب ملف PDF من قيود')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->visible(fn (): bool => empty($this->record->pdf_url))
                ->action(function () {
                    /** @var QoyodInvoice $record */
                    $record = $this->record;
                    $service = app(QoyodInvoiceSyncService::class);
                    $freshUrl = $service->fetchInvoicePdfUrl($record->qoyod_invoice_id);
                    if ($freshUrl) {
                        $record->update(['pdf_url' => $freshUrl]);
                        $this->redirect($freshUrl, navigate: false);
                    } else {
                        Notification::make()
                            ->title('تعذر الحصول على رابط ملف PDF من قيود')
                            ->warning()
                            ->send();
                    }
                }),

            Action::make('refresh_from_qoyod')
                ->label('إعادة جلب من قيود الآن')
                ->icon('heroicon-o-arrow-path')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('تحديث بيانات الفاتورة من قيود')
                ->modalDescription('سيتم جلب أحدث تفاصيل الفاتورة والمبالغ ورابط PDF من قيود دون إنشاء أي فاتورة جديدة.')
                ->modalSubmitActionLabel('تحديث الآن')
                ->modalCancelActionLabel('إلغاء')
                ->action(function () {
                    /** @var QoyodInvoice $record */
                    $record = $this->record;
                    $service = app(QoyodInvoiceSyncService::class);
                    $updated = $service->fetchAndStoreInvoiceDetails($record->qoyod_invoice_id, $record->order, $record->sync);

                    if ($updated && $updated->fetch_status === QoyodInvoice::FETCH_STATUS_SUCCESS) {
                        Notification::make()
                            ->title('تم تحديث بيانات الفاتورة بنجاح')
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('تعذر جلب تفاصيل الفاتورة')
                            ->body($updated?->fetch_error ?? 'خطأ غير معروف')
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}

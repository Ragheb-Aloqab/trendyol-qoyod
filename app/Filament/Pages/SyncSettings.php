<?php

namespace App\Filament\Pages;

use App\Models\AppSetting;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;

class SyncSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'مزامنة ترينديول وقيود';

    protected static ?string $navigationLabel = 'إعدادات المزامنة';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.sync-settings';

    public function getMaxContentWidth(): Width|string|null
    {
        return Width::Full;
    }

    public bool $auto_sync_enabled = true;

    public ?string $sync_start_date = '2026-10-01';

    public array $approved_statuses = ['Delivered'];

    public bool $auto_send_to_qoyod = true;

    public function getTitle(): string|Htmlable
    {
        return 'إعدادات وقواعد المزامنة';
    }

    public function getSubheading(): ?string
    {
        return 'التحكم في تشغيل وإيقاف المزامنة التلقائية، تقييد تاريخ البدء لمنع الطلبات القديمة، وتحديد حالات الطلب المعتمدة لقيود.';
    }

    public function mount(): void
    {
        $this->auto_sync_enabled = (bool) AppSetting::get('auto_sync_enabled', config('services.trendyol.auto_sync_enabled', true));
        $this->sync_start_date = (string) AppSetting::get('sync_start_date', config('services.trendyol.sync_start_date', '2026-10-01'));
        $this->approved_statuses = (array) AppSetting::get('approved_statuses', config('services.trendyol.approved_statuses', ['Delivered']));
        $this->auto_send_to_qoyod = (bool) AppSetting::get('auto_send_to_qoyod', config('services.trendyol.auto_send_to_qoyod', true));
    }

    public function save(): void
    {
        $startDate = !empty($this->sync_start_date) ? $this->sync_start_date : '2026-10-01';
        $statuses = !empty($this->approved_statuses) ? $this->approved_statuses : ['Delivered'];

        AppSetting::set('auto_sync_enabled', $this->auto_sync_enabled);
        AppSetting::set('sync_start_date', $startDate);
        AppSetting::set('approved_statuses', array_values($statuses));
        AppSetting::set('auto_send_to_qoyod', $this->auto_send_to_qoyod);

        Notification::make()
            ->title('تم حفظ إعدادات المزامنة بنجاح')
            ->body('تم تطبيق القواعد الجديدة: المزامنة التلقائية ' . ($this->auto_sync_enabled ? 'مفعلة' : 'معطلة') . '، وبدء المزامنة من تاريخ ' . $startDate)
            ->success()
            ->send();
    }
}

<?php

namespace App\Filament\Widgets;

use App\Models\QoyodSync;
use App\Models\TrendyolOrder;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SyncStatsOverviewWidget extends BaseWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $totalOrders = TrendyolOrder::count();
        $syncedInvoices = QoyodSync::where('type', QoyodSync::TYPE_INVOICE)->where('status', QoyodSync::STATUS_SUCCESS)->count();
        $pendingInvoices = QoyodSync::where('status', QoyodSync::STATUS_PENDING)->count();
        $failedInvoices = QoyodSync::where('status', QoyodSync::STATUS_FAILED)->count();

        return [
            Stat::make('الطلبات في قاعدة البيانات', number_format($totalOrders))
                ->description('إجمالي الشحنات المستوردة')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('primary')
                ->chart([0, 0, 0, $totalOrders]),

            Stat::make('فواتير مزامنة بنجاح', number_format($syncedInvoices))
                ->description('تم إنشاؤها في حساب قيود')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success')
                ->chart([0, 0, 0, $syncedInvoices]),

            Stat::make('فواتير قيد الانتظار', number_format($pendingInvoices))
                ->description('في طابور المزامنة التلقائية')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('فواتير فشلت مزامنتها', number_format($failedInvoices))
                ->description($failedInvoices > 0 ? 'بحاجة لمعالجة وإعادة محاولة' : 'لا توجد أخطاء مزامنة')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($failedInvoices > 0 ? 'danger' : 'gray'),
        ];
    }
}

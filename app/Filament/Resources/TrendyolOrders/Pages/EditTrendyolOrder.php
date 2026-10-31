<?php

namespace App\Filament\Resources\TrendyolOrders\Pages;

use App\Filament\Resources\TrendyolOrders\TrendyolOrderResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditTrendyolOrder extends EditRecord
{
    protected static string $resource = TrendyolOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}

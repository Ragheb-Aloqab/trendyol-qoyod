<?php

namespace App\Filament\Resources\QoyodSyncs\Pages;

use App\Filament\Resources\QoyodSyncs\QoyodSyncResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditQoyodSync extends EditRecord
{
    protected static string $resource = QoyodSyncResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}

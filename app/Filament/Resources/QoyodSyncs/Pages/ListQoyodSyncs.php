<?php

namespace App\Filament\Resources\QoyodSyncs\Pages;

use App\Filament\Resources\QoyodSyncs\QoyodSyncResource;
use Filament\Resources\Pages\ListRecords;

class ListQoyodSyncs extends ListRecords
{
    protected static string $resource = QoyodSyncResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Read-only
        ];
    }
}

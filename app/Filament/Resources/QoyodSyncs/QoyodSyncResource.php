<?php

namespace App\Filament\Resources\QoyodSyncs;

use App\Filament\Resources\QoyodSyncs\Pages\ListQoyodSyncs;
use App\Filament\Resources\QoyodSyncs\Pages\ViewQoyodSync;
use App\Filament\Resources\QoyodSyncs\Schemas\QoyodSyncInfolist;
use App\Filament\Resources\QoyodSyncs\Tables\QoyodSyncsTable;
use App\Models\QoyodSync;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class QoyodSyncResource extends Resource
{
    protected static ?string $model = QoyodSync::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPathRoundedSquare;

    protected static string|UnitEnum|null $navigationGroup = 'مزامنة ترينديول وقيود';

    protected static ?string $navigationLabel = 'سجلات المزامنة مع قيود';

    protected static ?int $navigationSort = 2;

    public static function getModelLabel(): string
    {
        return 'سجل مزامنة';
    }

    public static function getPluralModelLabel(): string
    {
        return 'سجلات المزامنة مع قيود';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return QoyodSyncInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return QoyodSyncsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListQoyodSyncs::route('/'),
            'view' => ViewQoyodSync::route('/{record}'),
        ];
    }
}

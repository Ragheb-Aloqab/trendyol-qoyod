<?php

namespace App\Filament\Resources\TrendyolOrders;

use App\Filament\Resources\TrendyolOrders\Pages\ListTrendyolOrders;
use App\Filament\Resources\TrendyolOrders\Pages\ViewTrendyolOrder;
use App\Filament\Resources\TrendyolOrders\Schemas\TrendyolOrderInfolist;
use App\Filament\Resources\TrendyolOrders\Tables\TrendyolOrdersTable;
use App\Models\TrendyolOrder;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class TrendyolOrderResource extends Resource
{
    protected static ?string $model = TrendyolOrder::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static string|UnitEnum|null $navigationGroup = 'مزامنة ترينديول وقيود';

    protected static ?string $navigationLabel = 'طلبات ترينديول';

    protected static ?int $navigationSort = 1;

    public static function getModelLabel(): string
    {
        return 'طلب ترينديول';
    }

    public static function getPluralModelLabel(): string
    {
        return 'طلبات ترينديول';
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
        return TrendyolOrderInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TrendyolOrdersTable::configure($table);
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
            'index' => ListTrendyolOrders::route('/'),
            'view' => ViewTrendyolOrder::route('/{record}'),
        ];
    }
}

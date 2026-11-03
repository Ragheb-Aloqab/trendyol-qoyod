<?php

namespace App\Filament\Resources\QoyodInvoices;

use App\Filament\Resources\QoyodInvoices\Pages\ListQoyodInvoices;
use App\Filament\Resources\QoyodInvoices\Pages\ViewQoyodInvoice;
use App\Filament\Resources\QoyodInvoices\Schemas\QoyodInvoiceInfolist;
use App\Filament\Resources\QoyodInvoices\Tables\QoyodInvoicesTable;
use App\Models\QoyodInvoice;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class QoyodInvoiceResource extends Resource
{
    protected static ?string $model = QoyodInvoice::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|UnitEnum|null $navigationGroup = 'مزامنة ترينديول وقيود';

    protected static ?string $navigationLabel = 'فواتير قيود';

    protected static ?int $navigationSort = 3;

    public static function getModelLabel(): string
    {
        return 'فاتورة قيود';
    }

    public static function getPluralModelLabel(): string
    {
        return 'فواتير قيود';
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
        return QoyodInvoiceInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return QoyodInvoicesTable::configure($table);
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
            'index' => ListQoyodInvoices::route('/'),
            'view' => ViewQoyodInvoice::route('/{record}'),
        ];
    }
}

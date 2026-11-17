<?php

namespace App\Filament\Resources\Tickets;

use App\Filament\Resources\Tickets\Pages\CreateTicket;
use App\Filament\Resources\Tickets\Pages\EditTicket;
use App\Filament\Resources\Tickets\Pages\ListTickets;
use App\Filament\Support\ProductForm;
use App\Filament\Support\ProductTable;
use App\Models\Product;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class TicketResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $slug = 'tickets';
    protected static ?string $modelLabel = 'ticket';
    protected static ?string $navigationLabel = 'Tickets';
    protected static ?string $recordTitleAttribute = 'title';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;
    protected static string|UnitEnum|null $navigationGroup = 'Catalog';
    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('type', 'ticket');
    }

    public static function form(Schema $schema): Schema
    {
        return ProductForm::configure($schema, 'ticket');
    }

    public static function table(Table $table): Table
    {
        return ProductTable::configure($table, 'ticket');
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getEloquentQuery()->where('is_active', true)->count();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTickets::route('/'),
            'create' => CreateTicket::route('/create'),
            'edit' => EditTicket::route('/{record}/edit'),
        ];
    }
}

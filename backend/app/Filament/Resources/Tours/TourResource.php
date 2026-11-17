<?php

namespace App\Filament\Resources\Tours;

use App\Filament\Resources\Tours\Pages\CreateTour;
use App\Filament\Resources\Tours\Pages\EditTour;
use App\Filament\Resources\Tours\Pages\ListTours;
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

class TourResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $slug = 'tours';
    protected static ?string $modelLabel = 'tour';
    protected static ?string $navigationLabel = 'Tours';
    protected static ?string $recordTitleAttribute = 'title';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSun;
    protected static string|UnitEnum|null $navigationGroup = 'Catalog';
    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('type', 'tour');
    }

    public static function form(Schema $schema): Schema
    {
        return ProductForm::configure($schema, 'tour');
    }

    public static function table(Table $table): Table
    {
        return ProductTable::configure($table, 'tour');
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getEloquentQuery()->where('is_active', true)->count();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTours::route('/'),
            'create' => CreateTour::route('/create'),
            'edit' => EditTour::route('/{record}/edit'),
        ];
    }
}

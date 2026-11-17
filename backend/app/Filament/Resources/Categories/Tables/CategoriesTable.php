<?php

namespace App\Filament\Resources\Categories\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                ColorColumn::make('color')->label(''),
                TextColumn::make('name')->weight('semibold')->searchable()->description(fn ($record) => $record->blurb),
                TextColumn::make('products_count')->counts('products')->label('Experiences')->badge(),
                ToggleColumn::make('is_active')->label('Visible'),
            ])
            ->recordActions([EditAction::make()]);
    }
}

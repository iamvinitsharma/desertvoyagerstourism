<?php

namespace App\Filament\Support;

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

/** Shared list table for tours and tickets. */
class ProductTable
{
    public static function configure(Table $table, string $type): Table
    {
        $path = $type === 'tour' ? 'tours' : 'tickets';

        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                ImageColumn::make('cover_image')->label('')->disk('public')->square()->imageSize(52),
                TextColumn::make('title')->searchable()->sortable()->weight('semibold')->wrap()
                    ->description(fn ($record) => collect([$record->location, $record->duration])->filter()->join(' · ')),
                TextColumn::make('category.name')->badge()->color('gray')->sortable(),
                TextColumn::make('price')->label('From')->money('AED')->sortable()
                    ->description(fn ($record) => $record->old_price ? 'was AED ' . number_format($record->old_price) : null),
                TextColumn::make('badge')->badge()->color('warning')->placeholder('—')->toggleable(),
                TextColumn::make('rating')->formatStateUsing(fn ($state, $record) => "★ {$state} ({$record->reviews_count})")->toggleable(),
                ToggleColumn::make('is_featured')->label('Featured'),
                ToggleColumn::make('is_active')->label('Published'),
                TextColumn::make('updated_at')->since()->label('Updated')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')->relationship('category', 'name'),
                TernaryFilter::make('is_featured')->label('Featured'),
                TernaryFilter::make('is_active')->label('Published'),
            ])
            ->recordActions([
                Action::make('site')->label('View')->icon('heroicon-o-arrow-top-right-on-square')->color('gray')
                    ->url(fn ($record) => rtrim(config('app.frontend_url'), '/') . "/{$path}/{$record->slug}")
                    ->openUrlInNewTab(),
                EditAction::make(),
                ReplicateAction::make()->label('Duplicate')
                    ->beforeReplicaSaved(function ($replica) {
                        $replica->title .= ' (copy)';
                        $replica->slug = Str::slug($replica->title) . '-' . Str::lower(Str::random(4));
                        $replica->is_active = false;
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('publish')->label('Publish')->icon('heroicon-o-eye')
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => true])),
                    BulkAction::make('unpublish')->label('Unpublish')->icon('heroicon-o-eye-slash')
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => false])),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

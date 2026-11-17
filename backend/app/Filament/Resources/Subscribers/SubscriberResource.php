<?php

namespace App\Filament\Resources\Subscribers;

use App\Filament\Resources\Subscribers\Pages\ListSubscribers;
use App\Models\Subscriber;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class SubscriberResource extends Resource
{
    protected static ?string $model = Subscriber::class;
    protected static ?string $navigationLabel = 'Newsletter';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;
    protected static string|UnitEnum|null $navigationGroup = 'Marketing';

    public static function canCreate(): bool { return false; }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('email')->searchable()->copyable()->weight('semibold'),
                TextColumn::make('source')->badge()->color('gray')->placeholder('Website'),
                TextColumn::make('created_at')->label('Subscribed')->since()->sortable(),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ListSubscribers::route('/')];
    }
}

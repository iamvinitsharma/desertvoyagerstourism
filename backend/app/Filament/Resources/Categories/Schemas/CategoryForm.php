<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CategoryForm
{
    public const ICONS = [
        'desert-safari' => '4x4 on dunes', 'city-tours' => 'City skyline', 'tickets' => 'Ticket',
        'theme-parks' => 'Ferris wheel', 'water' => 'Dhow boat', 'adventure' => 'Sun over dunes',
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(2)->columnSpanFull()->schema([
                TextInput::make('name')->required()->live(onBlur: true)
                    ->afterStateUpdated(function (Set $set, ?string $state, string $operation) {
                        if ($operation === 'create') $set('slug', Str::slug((string) $state));
                    }),
                TextInput::make('slug')->required()->unique(ignoreRecord: true),
                TextInput::make('blurb')->placeholder('Dune bashing, camels & BBQ camps')->columnSpanFull(),
                Select::make('icon')->options(self::ICONS)->required()->helperText('Illustration shown on the homepage tile.'),
                ColorPicker::make('color')->label('Tile colour'),
                TextInput::make('sort_order')->numeric()->default(0),
                Toggle::make('is_active')->label('Show on website')->default(true)->inline(false),
            ]),
        ]);
    }
}

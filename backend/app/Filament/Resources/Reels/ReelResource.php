<?php

namespace App\Filament\Resources\Reels;

use App\Filament\Resources\Reels\Pages\CreateReel;
use App\Filament\Resources\Reels\Pages\EditReel;
use App\Filament\Resources\Reels\Pages\ListReels;
use App\Models\Reel;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

class ReelResource extends Resource
{
    protected static ?string $model = Reel::class;
    protected static ?string $navigationLabel = 'Video reels';
    protected static ?string $recordTitleAttribute = 'title';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFilm;
    protected static string|UnitEnum|null $navigationGroup = 'Content';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(2)->columnSpanFull()->schema([
                TextInput::make('title')->required()->placeholder('Dune bashing at sunset'),
                TextInput::make('guest')->placeholder('Family from UK'),
                Select::make('product_id')->label('Linked tour / ticket')->relationship('product', 'title')->searchable()->preload(),
                TextInput::make('duration')->placeholder('0:32'),
                FileUpload::make('poster')->label('Cover image (vertical)')->image()->disk('public')->directory('reels')->imageEditor()->required(),
                FileUpload::make('video')->label('Video (MP4)')->disk('public')->directory('reels')
                    ->acceptedFileTypes(['video/mp4'])->maxSize(51200)->helperText('Or use a YouTube video below.'),
                TextInput::make('youtube_id')->label('YouTube / Shorts video ID')->placeholder('pgq0S8YABu0'),
                TextInput::make('sort_order')->numeric()->default(0),
                Toggle::make('is_published')->label('Published')->default(true),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                ImageColumn::make('poster')->label('')->disk('public')->imageHeight(64)->extraImgAttributes(['class' => 'rounded-lg']),
                TextColumn::make('title')->weight('semibold')->description(fn ($record) => $record->guest)->searchable(),
                TextColumn::make('product.title')->label('Tour / ticket')->wrap(),
                TextColumn::make('source')->state(fn ($record) => $record->video ? 'MP4 upload' : ($record->youtube_id ? 'YouTube' : '—'))->badge()->color('gray'),
                TextColumn::make('duration'),
                ToggleColumn::make('is_published')->label('Published'),
            ])
            ->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ListReels::route('/'), 'create' => CreateReel::route('/create'), 'edit' => EditReel::route('/{record}/edit')];
    }
}

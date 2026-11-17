<?php

namespace App\Filament\Resources\Reviews;

use App\Filament\Resources\Reviews\Pages\CreateReview;
use App\Filament\Resources\Reviews\Pages\EditReview;
use App\Filament\Resources\Reviews\Pages\ListReviews;
use App\Models\Review;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use UnitEnum;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;
    protected static ?string $recordTitleAttribute = 'name';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;
    protected static string|UnitEnum|null $navigationGroup = 'Content';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(2)->columnSpanFull()->schema([
                TextInput::make('name')->required()->placeholder('Sarah M.'),
                TextInput::make('country')->placeholder('United Kingdom'),
                Select::make('rating')->options([5 => '★★★★★ 5', 4 => '★★★★ 4', 3 => '★★★ 3', 2 => '★★ 2', 1 => '★ 1'])->default(5)->required(),
                Select::make('product_id')->label('Tour / ticket')->relationship('product', 'title')->searchable()->preload(),
                TextInput::make('title')->label('Headline')->columnSpanFull(),
                Textarea::make('text')->label('Review')->rows(5)->required()->columnSpanFull(),
                DatePicker::make('review_date')->native(false)->default(today()),
                Select::make('source')->options(['Website' => 'Website', 'Google' => 'Google', 'Tripadvisor' => 'Tripadvisor', 'WhatsApp' => 'WhatsApp'])->default('Website'),
                Toggle::make('is_published')->label('Published')->default(true),
                Toggle::make('is_featured')->label('Show on homepage'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('review_date', 'desc')
            ->columns([
                TextColumn::make('name')->weight('semibold')->searchable()->description(fn ($record) => $record->country),
                TextColumn::make('rating')->formatStateUsing(fn ($state) => str_repeat('★', $state) . str_repeat('☆', 5 - $state))->color('warning'),
                TextColumn::make('title')->wrap()->description(fn ($record) => \Illuminate\Support\Str::limit($record->text, 90))->searchable(['title', 'text']),
                TextColumn::make('product.title')->label('Tour / ticket')->wrap()->toggleable(),
                TextColumn::make('review_date')->date('M Y')->sortable(),
                ToggleColumn::make('is_featured')->label('Homepage'),
                ToggleColumn::make('is_published')->label('Published'),
            ])
            ->filters([
                SelectFilter::make('rating')->options([5 => '5 stars', 4 => '4 stars', 3 => '3 stars', 2 => '2 stars', 1 => '1 star']),
                TernaryFilter::make('is_published')->label('Published'),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ListReviews::route('/'), 'create' => CreateReview::route('/create'), 'edit' => EditReview::route('/{record}/edit')];
    }
}

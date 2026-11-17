<?php

namespace App\Filament\Resources\Availabilities;

use App\Filament\Resources\Availabilities\Pages\CreateAvailability;
use App\Filament\Resources\Availabilities\Pages\EditAvailability;
use App\Filament\Resources\Availabilities\Pages\ListAvailabilities;
use App\Models\Availability;
use App\Models\Product;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class AvailabilityResource extends Resource
{
    protected static ?string $model = Availability::class;
    protected static ?string $navigationLabel = 'Availability';
    protected static ?string $modelLabel = 'availability rule';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;
    protected static string|UnitEnum|null $navigationGroup = 'Catalog';
    protected static ?int $navigationSort = 4;

    public const STATUS = ['soldout' => 'Sold out', 'limited' => 'Few left', 'available' => 'Available'];

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->description('Every date is available by default. Add a rule to mark a day (or one time slot) as sold out or few left.')
                ->columns(2)->columnSpanFull()->schema([
                    Select::make('product_id')->label('Tour / ticket')->relationship('product', 'title')->searchable()->preload()->required()->live(),
                    DatePicker::make('date')->required()->native(false)->minDate(today())->displayFormat('D, d M Y'),
                    Select::make('time_slot')->label('Time slot')->placeholder('Whole day')
                        ->options(fn (Get $get) => collect(Product::find($get('product_id'))?->time_slots ?? [])->mapWithKeys(fn ($s) => [$s => $s])->all())
                        ->helperText('Tickets only. Leave empty to apply to the whole day.'),
                    Select::make('status')->options(self::STATUS)->default('soldout')->required(),
                    TextInput::make('note')->placeholder('Private event, fully booked…')->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('date')
            ->modifyQueryUsing(fn (Builder $query) => $query->with('product'))
            ->columns([
                TextColumn::make('date')->date('D, d M Y')->sortable(),
                TextColumn::make('product.title')->label('Tour / ticket')->searchable()->wrap(),
                TextColumn::make('time_slot')->label('Slot')->placeholder('Whole day'),
                TextColumn::make('status')->badge()->formatStateUsing(fn ($state) => self::STATUS[$state] ?? $state)
                    ->color(fn ($state) => match ($state) { 'soldout' => 'danger', 'limited' => 'warning', default => 'success' }),
                TextColumn::make('note')->placeholder('—')->wrap(),
            ])
            ->filters([
                SelectFilter::make('product')->relationship('product', 'title')->searchable()->preload(),
                SelectFilter::make('status')->options(self::STATUS),
                Filter::make('upcoming')->label('Upcoming only')->default()->query(fn (Builder $query) => $query->whereDate('date', '>=', today())),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAvailabilities::route('/'),
            'create' => CreateAvailability::route('/create'),
            'edit' => EditAvailability::route('/{record}/edit'),
        ];
    }
}

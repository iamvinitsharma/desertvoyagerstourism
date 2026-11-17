<?php

namespace App\Filament\Resources\Orders;

use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Orders\Pages\ViewOrder;
use App\Models\Order;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $slug = 'ticket-orders';
    protected static ?string $navigationLabel = 'Ticket orders';
    protected static ?string $modelLabel = 'ticket order';
    protected static ?string $recordTitleAttribute = 'ref';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;
    protected static string|UnitEnum|null $navigationGroup = 'Bookings';
    protected static ?int $navigationSort = 2;

    public static function statusColor(?string $state): string
    {
        return match ($state) {
            'paid' => 'success', 'pending' => 'warning', 'failed' => 'danger',
            'refunded' => 'info', 'cancelled' => 'gray', default => 'gray',
        };
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Order')->columns(2)->columnSpanFull()->schema([
                Select::make('status')->options(Order::STATUSES)->required()
                    ->helperText('Normally set automatically by the payment gateway.'),
                Toggle::make('tickets_sent')->label('E-tickets sent to customer')->inline(false),
                Textarea::make('admin_notes')->label('Internal notes')->rows(3)->columnSpanFull(),
            ]),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(3)->columnSpanFull()->schema([
                Section::make('Tickets')->columnSpan(2)->schema([
                    RepeatableEntry::make('items')->hiddenLabel()->columns(4)->schema([
                        TextEntry::make('title')->hiddenLabel()->weight('semibold')->columnSpan(2),
                        TextEntry::make('date')->hiddenLabel()->date('D, d M Y')
                            ->suffix(fn ($record) => $record->time_slot ? ' · ' . $record->time_slot : ''),
                        TextEntry::make('amount')->hiddenLabel()->money('AED')->alignEnd(),
                        TextEntry::make('qty')->hiddenLabel()->columnSpanFull()->color('gray')
                            ->state(fn ($record) => collect([
                                $record->adults ? "{$record->adults} adult" : null,
                                $record->children ? "{$record->children} child" : null,
                                $record->infants ? "{$record->infants} infant" : null,
                            ])->filter()->join(', ')),
                    ]),
                    TextEntry::make('total')->label('Total')->money('AED')->weight('bold')->size('lg'),
                ]),
                Section::make('Order')->schema([
                    TextEntry::make('ref')->label('Reference')->copyable()->weight('bold'),
                    TextEntry::make('status')->badge()->formatStateUsing(fn ($state) => Order::STATUSES[$state] ?? $state)
                        ->color(fn ($state) => self::statusColor($state)),
                    TextEntry::make('paid_at')->label('Paid')->dateTime('d M Y, H:i')->placeholder('Not paid'),
                    TextEntry::make('payment_reference')->label('Gateway reference')->placeholder('—')->copyable(),
                    IconEntry::make('tickets_sent')->label('E-tickets sent')->boolean(),
                    TextEntry::make('customer_name')->label('Customer')->weight('semibold'),
                    TextEntry::make('phone')->copyable()->url(fn (Order $r) => 'https://wa.me/' . preg_replace('/\D+/', '', $r->phone), true)->color('success'),
                    TextEntry::make('email')->copyable()->url(fn (Order $r) => "mailto:{$r->email}"),
                    TextEntry::make('notes')->label('Customer notes')->placeholder('—'),
                    TextEntry::make('admin_notes')->label('Internal notes')->placeholder('—'),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount('items')->with('items'))
            ->columns([
                TextColumn::make('ref')->label('Ref')->searchable()->copyable()->weight('semibold')->fontFamily('mono'),
                TextColumn::make('customer_name')->label('Customer')->searchable(['first_name', 'last_name', 'email', 'phone'])
                    ->description(fn (Order $r) => $r->email),
                TextColumn::make('tickets_summary')->label('Tickets')->wrap()
                    ->state(fn (Order $r) => $r->items->map(fn ($i) => $i->title . ' · ' . $i->date->format('d M'))->join(' | ')),
                TextColumn::make('total')->money('AED')->sortable()->weight('semibold'),
                TextColumn::make('status')->badge()->formatStateUsing(fn ($state) => Order::STATUSES[$state] ?? $state)
                    ->color(fn ($state) => self::statusColor($state)),
                IconColumn::make('tickets_sent')->label('Sent')->boolean(),
                TextColumn::make('created_at')->label('Placed')->since()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(Order::STATUSES)->multiple(),
                Filter::make('today')->label('Placed today')->query(fn (Builder $query) => $query->whereDate('created_at', today())),
            ])
            ->recordActions([
                Action::make('sent')->label('Mark e-tickets sent')->icon('heroicon-o-paper-airplane')->color('success')
                    ->visible(fn (Order $r) => $r->status === 'paid' && ! $r->tickets_sent)
                    ->action(function (Order $r) {
                        $r->update(['tickets_sent' => true]);
                        Notification::make()->success()->title("{$r->ref}: e-tickets marked as sent")->send();
                    }),
                ViewAction::make(),
                EditAction::make(),
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        $n = Order::where('status', 'paid')->where('tickets_sent', false)->count();
        return $n ? (string) $n : null;
    }

    public static function getNavigationBadgeColor(): ?string { return 'success'; }
    public static function getNavigationBadgeTooltip(): ?string { return 'Paid orders waiting for e-tickets'; }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'view' => ViewOrder::route('/{record}'),
            'edit' => EditOrder::route('/{record}/edit'),
        ];
    }
}

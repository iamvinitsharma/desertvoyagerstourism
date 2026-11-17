<?php

namespace App\Filament\Resources\Enquiries;

use App\Filament\Resources\Enquiries\Pages\EditEnquiry;
use App\Filament\Resources\Enquiries\Pages\ListEnquiries;
use App\Filament\Resources\Enquiries\Pages\ViewEnquiry;
use App\Models\Enquiry;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class EnquiryResource extends Resource
{
    protected static ?string $model = Enquiry::class;
    protected static ?string $slug = 'tour-requests';
    protected static ?string $navigationLabel = 'Tour requests';
    protected static ?string $modelLabel = 'tour request';
    protected static ?string $recordTitleAttribute = 'ref';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxArrowDown;
    protected static string|UnitEnum|null $navigationGroup = 'Bookings';
    protected static ?int $navigationSort = 1;

    public static function statusColor(?string $state): string
    {
        return match ($state) {
            'new' => 'warning', 'contacted' => 'info', 'confirmed' => 'success',
            'completed' => 'gray', 'cancelled' => 'danger', default => 'gray',
        };
    }

    /** WhatsApp chat link with a friendly prefilled reply. */
    public static function whatsappUrl(Enquiry $r): string
    {
        $phone = preg_replace('/\D+/', '', $r->phone);
        $msg = "Hi {$r->customer_name}, thank you for your booking request ({$r->ref}) for {$r->tour_title} on "
            . $r->date->format('D, d M Y') . ". ";
        return "https://wa.me/{$phone}?text=" . rawurlencode($msg);
    }

    public static function statusActions(): array
    {
        $make = fn (string $status, string $label, string $icon, string $color) => Action::make("mark_{$status}")
            ->label($label)->icon($icon)->color($color)
            ->visible(fn (Enquiry $record) => $record->status !== $status)
            ->action(function (Enquiry $record) use ($status, $label) {
                $record->update(['status' => $status]);
                Notification::make()->success()->title("{$record->ref} marked as " . strtolower(Enquiry::STATUSES[$status]))->send();
            });

        return [
            $make('contacted', 'Mark contacted', 'heroicon-o-chat-bubble-left-right', 'info'),
            $make('confirmed', 'Confirm booking', 'heroicon-o-check-badge', 'success'),
            $make('completed', 'Mark completed', 'heroicon-o-flag', 'gray'),
            $make('cancelled', 'Cancel', 'heroicon-o-x-circle', 'danger'),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Booking')->columns(3)->columnSpanFull()->schema([
                Select::make('status')->options(Enquiry::STATUSES)->required(),
                DatePicker::make('date')->label('Tour date')->native(false)->required(),
                TextInput::make('pickup_time')->placeholder('3:30 pm from hotel lobby'),
                TextInput::make('adults')->numeric()->minValue(1),
                TextInput::make('children')->numeric()->minValue(0),
                TextInput::make('infants')->numeric()->minValue(0),
                TextInput::make('estimated_total')->numeric()->prefix('AED'),
                TextInput::make('hotel')->label('Hotel / pickup location')->columnSpan(2),
            ]),
            Section::make('Customer')->columns(3)->columnSpanFull()->schema([
                TextInput::make('customer_name')->required(),
                TextInput::make('phone')->required(),
                TextInput::make('email')->email()->required(),
                Textarea::make('notes')->label('Customer notes')->rows(2)->columnSpanFull(),
            ]),
            Section::make('Internal notes')->columnSpanFull()->schema([
                Textarea::make('admin_notes')->hiddenLabel()->rows(3)->placeholder('Only visible to your team'),
            ]),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(3)->columnSpanFull()->schema([
                Section::make('Booking')->columnSpan(2)->columns(2)->schema([
                    TextEntry::make('tour_title')->label('Tour')->weight('semibold')->columnSpanFull(),
                    TextEntry::make('date')->label('Tour date')->date('l, d F Y'),
                    TextEntry::make('guests')->label('Guests'),
                    TextEntry::make('addons')->label('Extras')->placeholder('None')->badge()->color('gray'),
                    TextEntry::make('estimated_total')->label('Estimated total')->money('AED')->weight('bold'),
                    TextEntry::make('hotel')->label('Hotel / pickup')->placeholder('—'),
                    TextEntry::make('pickup_time')->placeholder('Not set yet'),
                    TextEntry::make('notes')->label('Customer notes')->placeholder('—')->columnSpanFull(),
                ]),
                Section::make('Status')->schema([
                    TextEntry::make('ref')->label('Reference')->copyable()->weight('bold'),
                    TextEntry::make('status')->badge()->formatStateUsing(fn ($state) => Enquiry::STATUSES[$state] ?? $state)
                        ->color(fn ($state) => self::statusColor($state)),
                    TextEntry::make('created_at')->label('Received')->dateTime('d M Y, H:i')->since(),
                    TextEntry::make('customer_name')->label('Customer')->weight('semibold'),
                    TextEntry::make('phone')->copyable()->url(fn (Enquiry $r) => self::whatsappUrl($r), true)->color('success'),
                    TextEntry::make('email')->copyable()->url(fn (Enquiry $r) => "mailto:{$r->email}"),
                    TextEntry::make('admin_notes')->label('Internal notes')->placeholder('—'),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('ref')->label('Ref')->searchable()->copyable()->weight('semibold')->fontFamily('mono'),
                TextColumn::make('tour_title')->label('Tour')->wrap()->searchable()->limit(40),
                TextColumn::make('date')->label('Tour date')->date('D, d M')->sortable()
                    ->color(fn (Enquiry $r) => $r->date->isToday() ? 'warning' : null),
                TextColumn::make('guests'),
                TextColumn::make('customer_name')->label('Customer')->searchable(['customer_name', 'phone', 'email'])
                    ->description(fn (Enquiry $r) => $r->phone),
                TextColumn::make('estimated_total')->label('Est. total')->money('AED')->sortable(),
                TextColumn::make('status')->badge()->formatStateUsing(fn ($state) => Enquiry::STATUSES[$state] ?? $state)
                    ->color(fn ($state) => self::statusColor($state)),
                TextColumn::make('created_at')->label('Received')->since()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(Enquiry::STATUSES)->multiple(),
                Filter::make('upcoming')->label('Upcoming tours')->query(fn (Builder $query) => $query->whereDate('date', '>=', today())),
                Filter::make('today')->label('Tours today')->query(fn (Builder $query) => $query->whereDate('date', today())),
            ])
            ->recordActions([
                Action::make('whatsapp')->label('WhatsApp')->icon('heroicon-o-chat-bubble-oval-left')->color('success')
                    ->url(fn (Enquiry $r) => self::whatsappUrl($r), true),
                ViewAction::make(),
                ActionGroup::make([...self::statusActions(), EditAction::make()]),
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        $n = Enquiry::where('status', 'new')->count();
        return $n ? (string) $n : null;
    }

    public static function getNavigationBadgeColor(): ?string { return 'warning'; }
    public static function getNavigationBadgeTooltip(): ?string { return 'New requests waiting for a reply'; }

    public static function getPages(): array
    {
        return [
            'index' => ListEnquiries::route('/'),
            'view' => ViewEnquiry::route('/{record}'),
            'edit' => EditEnquiry::route('/{record}/edit'),
        ];
    }
}

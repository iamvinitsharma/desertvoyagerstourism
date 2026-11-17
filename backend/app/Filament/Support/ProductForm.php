<?php

namespace App\Filament\Support;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

/** Shared create/edit form for tours and tickets (same `products` table). */
class ProductForm
{
    public const BADGES = ['Best Seller' => 'Best Seller', 'Featured' => 'Featured', 'Limited Offer' => 'Limited Offer', 'New' => 'New'];

    public const FEATURE_ICONS = [
        'car' => 'Car / pickup', 'sun' => 'Sun', 'mountain' => 'Dunes', 'users' => 'Group', 'clock' => 'Time',
        'landmark' => 'Landmark', 'shield' => 'Safety', 'zap' => 'Instant', 'ticket' => 'Ticket',
        'calendar' => 'Calendar', 'ferris' => 'Theme park', 'waves' => 'Water', 'star' => 'Star',
    ];

    public static function configure(Schema $schema, string $type): Schema
    {
        $isTour = $type === 'tour';
        $list = fn (string $name, string $label, string $placeholder) => Repeater::make($name)
            ->label($label)
            ->simple(TextInput::make('value')->required()->placeholder($placeholder))
            ->defaultItems(0)->reorderable()->addActionLabel('Add line');

        return $schema->components([
            Tabs::make('product')->persistTabInQueryString()->columnSpanFull()->tabs([

                Tab::make('Basics')->icon('heroicon-o-information-circle')->schema([
                    Section::make()->columns(2)->schema([
                        TextInput::make('title')->required()->maxLength(160)->columnSpanFull()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, ?string $state, string $operation) {
                                if ($operation === 'create') $set('slug', Str::slug((string) $state));
                            }),
                        TextInput::make('slug')->required()->unique(ignoreRecord: true)->maxLength(180)
                            ->prefix($isTour ? '/tours/' : '/tickets/')->helperText('Web address of this page.'),
                        Select::make('category_id')->label('Category')->relationship('category', 'name')
                            ->searchable()->preload()->required(),
                        TextInput::make('location')->placeholder('Abu Dhabi'),
                        TextInput::make('duration')->placeholder($isTour ? '6 hours' : 'Full day'),
                        TextInput::make('timing')->placeholder('3:00 pm – 9:00 pm')->visible($isTour),
                        Select::make('badge')->options(self::BADGES)->placeholder('No badge'),
                    ]),
                    Section::make('Visibility')->columns(4)->schema([
                        Toggle::make('is_active')->label('Published')->default(true),
                        Toggle::make('is_featured')->label('Featured on homepage'),
                        Toggle::make('hotel_pickup')->label('Hotel pickup')->visible($isTour)->default($isTour),
                        TextInput::make('sort_order')->numeric()->default(0)->label('Sort order'),
                    ]),
                    Section::make('Images')->schema([
                        FileUpload::make('cover_image')->label('Cover image')->image()->disk('public')
                            ->directory('products')->imageEditor()->maxSize(5120)
                            ->helperText('Shown on cards. Landscape, at least 1200×900.'),
                        FileUpload::make('gallery')->image()->multiple()->reorderable()->appendFiles()
                            ->disk('public')->directory('products')->panelLayout('grid')->maxFiles(12)->maxSize(5120),
                    ]),
                ]),

                Tab::make('Pricing & booking')->icon('heroicon-o-banknotes')->schema([
                    Section::make('Prices (AED)')->columns(4)->schema([
                        TextInput::make('price')->label($isTour ? 'Adult price' : 'Adult ticket')->numeric()->required()->prefix('AED'),
                        TextInput::make('old_price')->label('Was (struck-through)')->numeric()->prefix('AED')
                            ->helperText('Leave empty for no discount.'),
                        TextInput::make('child_price')->label('Child (3–11)')->numeric()->prefix('AED'),
                        TextInput::make('infant_price')->label('Infant (under 3)')->numeric()->prefix('AED')->default(0),
                    ]),
                    Section::make('Optional extras')->description('Add-ons guests can tick on the booking form.')->visible($isTour)->schema([
                        Repeater::make('addons')->hiddenLabel()->columns(5)->defaultItems(0)->addActionLabel('Add extra')
                            ->schema([
                                TextInput::make('key')->required()->label('Code')->placeholder('quad'),
                                TextInput::make('label')->required()->columnSpan(2)->placeholder('Quad bike (30 min)'),
                                TextInput::make('price')->numeric()->required()->prefix('AED'),
                                Select::make('per')->options(['person' => 'Per person', 'booking' => 'Per booking'])->default('person')->required(),
                                TextInput::make('sub')->label('Note')->columnSpanFull()->placeholder('Per rider'),
                            ]),
                    ]),
                    Section::make('Time slots')->description('Entry times guests choose from. Sold-out slots are set in Availability.')->visible(! $isTour)->schema([
                        TagsInput::make('time_slots')->hiddenLabel()->placeholder('Add a time, e.g. 10:00')->reorderable(),
                    ]),
                    Section::make('Booking rules')->columns(2)->schema([
                        TextInput::make('min_notice_days')->label('Book at least (days ahead)')->numeric()->default($isTour ? 1 : 0),
                        TextInput::make('daily_capacity')->label('Daily capacity')->numeric()->placeholder('Unlimited'),
                    ]),
                ]),

                Tab::make('Content')->icon('heroicon-o-document-text')->schema([
                    Section::make()->schema([
                        Textarea::make('overview')->rows(5),
                        TagsInput::make('highlights')->placeholder('Add a highlight'),
                        Repeater::make('features')->label('Feature icons')->columns(2)->defaultItems(0)->grid(2)
                            ->addActionLabel('Add feature')->schema([
                                Select::make('icon')->options(self::FEATURE_ICONS)->required(),
                                TextInput::make('label')->required(),
                            ]),
                    ]),
                    Grid::make(2)->schema([
                        Section::make('What’s included')->schema([$list('inclusions', 'Included', 'Hotel pickup in a 4x4')]),
                        Section::make('Not included')->schema([$list('exclusions', 'Excluded', 'Tips and personal expenses')]),
                    ]),
                    Section::make('Itinerary')->visible($isTour)->schema([
                        Repeater::make('itinerary')->hiddenLabel()->columns(4)->defaultItems(0)->reorderable()
                            ->addActionLabel('Add step')->schema([
                                TextInput::make('time')->placeholder('3:00 pm'),
                                TextInput::make('title')->required()->columnSpan(3),
                                Textarea::make('text')->rows(2)->columnSpanFull(),
                            ]),
                    ]),
                ]),

                Tab::make('Pickup & policies')->icon('heroicon-o-map-pin')->schema([
                    Section::make('Pickup')->columns(2)->visible($isTour)->schema([
                        TextInput::make('pickup_areas')->placeholder('Hotels and malls in Abu Dhabi city'),
                        TextInput::make('pickup_time')->placeholder('Confirmed on WhatsApp the day before'),
                    ]),
                    Section::make('Details')->columns(2)->schema([
                        TextInput::make('group_type')->placeholder($isTour ? 'Shared group (private available)' : 'Self-guided entry'),
                        TextInput::make('languages')->placeholder('English, Arabic'),
                    ]),
                    Grid::make(2)->schema([
                        Section::make('Good to know')->schema([$list('good_to_know', 'Notes', 'Wear comfortable shoes')]),
                        Section::make('Cancellation policy')->schema([$list('cancellation', 'Policy lines', 'Free cancellation up to 24 hours before')]),
                    ]),
                ]),

                Tab::make('FAQ')->icon('heroicon-o-question-mark-circle')->schema([
                    Repeater::make('faqs')->hiddenLabel()->defaultItems(0)->reorderable()->addActionLabel('Add question')
                        ->itemLabel(fn (array $state): ?string => $state['q'] ?? null)->collapsible()
                        ->schema([
                            TextInput::make('q')->label('Question')->required(),
                            Textarea::make('a')->label('Answer')->rows(3)->required(),
                        ]),
                ]),

                Tab::make('SEO & ratings')->icon('heroicon-o-magnifying-glass')->schema([
                    Section::make('Search engines')->schema([
                        TextInput::make('seo_title')->label('SEO title')->maxLength(70)->helperText('Leave empty to use the title.'),
                        Textarea::make('seo_description')->label('SEO description')->rows(2)->maxLength(320),
                    ]),
                    Section::make('Rating shown on the website')->columns(2)->schema([
                        TextInput::make('rating')->numeric()->minValue(0)->maxValue(5)->step(0.1)->default(5),
                        TextInput::make('reviews_count')->label('Number of reviews')->numeric()->default(0),
                    ]),
                ]),
            ]),
        ]);
    }
}

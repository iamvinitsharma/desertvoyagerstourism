<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class SiteSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;
    protected static string|UnitEnum|null $navigationGroup = 'Settings';
    protected static ?string $navigationLabel = 'Site settings';
    protected static ?string $title = 'Site settings';
    protected string $view = 'filament.pages.site-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(SiteSetting::current()->attributesToArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema->statePath('data')->components([
            Section::make('Contact details')->description('Shown in the header, footer and contact buttons.')->columns(2)->schema([
                TextInput::make('company_name')->required(),
                TextInput::make('phone')->tel()->placeholder('+971 58 572 1121'),
                TextInput::make('whatsapp')->label('WhatsApp number')->tel()->placeholder('971585721121')
                    ->helperText('Digits only, with country code.'),
                TextInput::make('email')->email(),
                TextInput::make('hours')->placeholder('Daily 10:00 am – 10:00 pm'),
                TextInput::make('address')->columnSpanFull(),
            ]),
            Section::make('Social media')->columns(3)->schema([
                TextInput::make('facebook')->url()->prefixIcon('heroicon-o-globe-alt'),
                TextInput::make('instagram')->url()->prefixIcon('heroicon-o-camera'),
                TextInput::make('youtube')->url()->prefixIcon('heroicon-o-play'),
            ]),
            Section::make('Award & rating')->columns(3)->schema([
                TextInput::make('award_name')->placeholder('Certificate of Excellence'),
                TextInput::make('award_year')->numeric()->placeholder('2026'),
                TextInput::make('award_url')->label('Tripadvisor listing URL')->url(),
                TextInput::make('rating_average')->label('Average rating')->numeric()->step(0.1)->maxValue(5),
                TextInput::make('rating_total')->label('Total reviews')->numeric(),
            ]),
            Section::make('Notifications')->schema([
                TextInput::make('notification_email')->label('Send new bookings to')->email()
                    ->helperText('Tour requests and ticket orders are emailed here.'),
            ]),
        ]);
    }

    public function save(): void
    {
        $data = collect($this->form->getState())->except(['id', 'created_at', 'updated_at'])->all();
        SiteSetting::current()->update($data);
        Notification::make()->success()->title('Settings saved')->send();
    }
}

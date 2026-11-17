<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Enquiries\EnquiryResource;
use App\Models\Enquiry;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestEnquiries extends TableWidget
{
    protected static ?int $sort = 3;
    protected static ?string $heading = 'Latest tour requests';
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => Enquiry::query()->latest()->limit(6))
            ->paginated(false)
            ->recordUrl(fn (Enquiry $r) => EnquiryResource::getUrl('view', ['record' => $r]))
            ->columns([
                TextColumn::make('ref')->weight('semibold')->fontFamily('mono'),
                TextColumn::make('tour_title')->label('Tour')->limit(38),
                TextColumn::make('date')->label('Tour date')->date('D, d M'),
                TextColumn::make('customer_name')->label('Customer')->description(fn (Enquiry $r) => $r->phone),
                TextColumn::make('guests'),
                TextColumn::make('status')->badge()->formatStateUsing(fn ($state) => Enquiry::STATUSES[$state] ?? $state)
                    ->color(fn ($state) => EnquiryResource::statusColor($state)),
                TextColumn::make('created_at')->label('Received')->since(),
            ]);
    }
}

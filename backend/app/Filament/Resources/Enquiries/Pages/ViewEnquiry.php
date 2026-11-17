<?php

namespace App\Filament\Resources\Enquiries\Pages;

use App\Filament\Resources\Enquiries\EnquiryResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewEnquiry extends ViewRecord
{
    protected static string $resource = EnquiryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('whatsapp')->label('Reply on WhatsApp')->icon('heroicon-o-chat-bubble-oval-left')->color('success')
                ->url(fn () => EnquiryResource::whatsappUrl($this->record), true),
            ActionGroup::make(EnquiryResource::statusActions())->label('Update status')->button()->icon('heroicon-o-arrow-path'),
            EditAction::make(),
        ];
    }
}

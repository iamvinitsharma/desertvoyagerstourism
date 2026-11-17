<?php

namespace App\Filament\Resources\Enquiries\Pages;

use App\Filament\Resources\Enquiries\EnquiryResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListEnquiries extends ListRecords
{
    protected static string $resource = EnquiryResource::class;

    public function getTabs(): array
    {
        $model = static::$resource::getModel();
        $tabs = ['all' => Tab::make('All')];
        foreach ($model::STATUSES as $key => $label) {
            $count = $model::where('status', $key)->count();
            $tabs[$key] = Tab::make($label)->badge($count ?: null)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', $key));
        }
        return $tabs;
    }
}

<?php

namespace App\Services;

use App\Models\Availability;
use App\Models\Product;
use Illuminate\Support\Carbon;

/** Every date/slot is available unless an Availability rule says otherwise. */
class AvailabilityService
{
    public const MAX_DAYS_AHEAD = 180;

    /** First bookable date for a product (respects "book at least X days ahead"). */
    public function minDate(Product $product): Carbon
    {
        return today()->addDays((int) $product->min_notice_days);
    }

    public function dayStatus(Product $product, string $date): string
    {
        $d = Carbon::parse($date)->startOfDay();
        if ($d->lt($this->minDate($product)) || $d->gt(today()->addDays(self::MAX_DAYS_AHEAD))) return 'past';

        return Availability::where('product_id', $product->id)->whereDate('date', $d)->whereNull('time_slot')
            ->value('status') ?? 'available';
    }

    public function slotStatus(Product $product, string $date, string $slot): string
    {
        $day = $this->dayStatus($product, $date);
        if (in_array($day, ['soldout', 'past'], true)) return 'soldout';

        return Availability::where('product_id', $product->id)->whereDate('date', $date)->where('time_slot', $slot)
            ->value('status') ?? 'available';
    }

    /** Overrides for the website calendar: ['days' => [date => status], 'slots' => ["date|slot" => status]]. */
    public function overrides(Product $product): array
    {
        $rules = Availability::where('product_id', $product->id)
            ->whereBetween('date', [today(), today()->addDays(self::MAX_DAYS_AHEAD)])->get();

        return [
            'minNoticeDays' => (int) $product->min_notice_days,
            'days' => $rules->whereNull('time_slot')->mapWithKeys(fn ($r) => [$r->date->toDateString() => $r->status])->all(),
            'slots' => $rules->whereNotNull('time_slot')->mapWithKeys(fn ($r) => [$r->date->toDateString() . '|' . $r->time_slot => $r->status])->all(),
        ];
    }
}

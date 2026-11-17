<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $guarded = [];

    protected $casts = [
        'price' => 'decimal:2', 'old_price' => 'decimal:2', 'child_price' => 'decimal:2', 'infant_price' => 'decimal:2',
        'rating' => 'decimal:1', 'is_featured' => 'boolean', 'is_active' => 'boolean', 'hotel_pickup' => 'boolean',
        'addons' => 'array', 'gallery' => 'array', 'highlights' => 'array', 'features' => 'array',
        'inclusions' => 'array', 'exclusions' => 'array', 'itinerary' => 'array', 'good_to_know' => 'array',
        'cancellation' => 'array', 'faqs' => 'array', 'time_slots' => 'array',
    ];

    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function availabilities(): HasMany { return $this->hasMany(Availability::class); }
    public function reviews(): HasMany { return $this->hasMany(Review::class); }

    public function scopeTours(Builder $q): Builder { return $q->where('type', 'tour'); }
    public function scopeTickets(Builder $q): Builder { return $q->where('type', 'ticket'); }
    public function scopeActive(Builder $q): Builder { return $q->where('is_active', true); }

    public function getDiscountPercentAttribute(): int
    {
        return $this->old_price && $this->old_price > $this->price
            ? (int) round((1 - $this->price / $this->old_price) * 100) : 0;
    }
}

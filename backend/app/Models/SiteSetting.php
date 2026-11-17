<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $guarded = [];
    protected $casts = ['rating_average' => 'decimal:1'];

    /** The single settings row (created on first use). */
    public static function current(): self
    {
        return static::query()->firstOrCreate([]);
    }
}

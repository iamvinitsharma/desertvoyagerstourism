<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Availability extends Model
{
    protected $guarded = [];
    protected $casts = ['date' => 'date'];

    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
}

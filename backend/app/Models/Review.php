<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $guarded = [];
    protected $casts = ['review_date' => 'date', 'is_published' => 'boolean', 'is_featured' => 'boolean'];

    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
}

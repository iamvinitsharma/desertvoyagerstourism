<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enquiry extends Model
{
    protected $guarded = [];
    protected $casts = ['date' => 'date', 'addons' => 'array', 'estimated_total' => 'decimal:2'];

    public const STATUSES = [
        'new' => 'New', 'contacted' => 'Contacted', 'confirmed' => 'Confirmed',
        'completed' => 'Completed', 'cancelled' => 'Cancelled',
    ];

    public function product(): BelongsTo { return $this->belongsTo(Product::class); }

    public function getGuestsAttribute(): string
    {
        return collect([
            $this->adults ? "{$this->adults} adult" . ($this->adults > 1 ? 's' : '') : null,
            $this->children ? "{$this->children} child" . ($this->children > 1 ? 'ren' : '') : null,
            $this->infants ? "{$this->infants} infant" . ($this->infants > 1 ? 's' : '') : null,
        ])->filter()->join(', ');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $guarded = [];
    protected $casts = ['total' => 'decimal:2', 'paid_at' => 'datetime', 'tickets_sent' => 'boolean'];

    public const STATUSES = [
        'pending' => 'Pending', 'paid' => 'Paid', 'failed' => 'Failed',
        'refunded' => 'Refunded', 'cancelled' => 'Cancelled',
    ];

    public function items(): HasMany { return $this->hasMany(OrderItem::class); }

    public function getCustomerNameAttribute(): string { return trim("{$this->first_name} {$this->last_name}"); }
}

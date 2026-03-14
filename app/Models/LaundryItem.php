<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaundryItem extends Model
{
    protected $fillable = [
        'laundry_order_id', 'garment_type', 'quantity', 'service_type', 'status',
    ];

    public static array $garmentTypes = ['shirt','trouser','dress','bedsheet','curtain','others'];
    public static array $statuses     = ['received','washing','drying','ironing','ready','delivered'];

    public function laundryOrder(): BelongsTo { return $this->belongsTo(LaundryOrder::class); }

    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->status) {
            'received'  => 'gray',
            'washing'   => 'blue',
            'drying'    => 'yellow',
            'ironing'   => 'orange',
            'ready'     => 'green',
            'delivered' => 'teal',
            default     => 'gray',
        };
    }
}

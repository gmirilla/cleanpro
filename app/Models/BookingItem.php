<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingItem extends Model
{
    protected $fillable = ['booking_id', 'service_id', 'price', 'quantity', 'subtotal'];

    protected $casts = [
        'price'    => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function booking(): BelongsTo { return $this->belongsTo(Booking::class); }
    public function service(): BelongsTo { return $this->belongsTo(Service::class); }

    protected static function boot(): void
    {
        parent::boot();
        static::saving(function (self $item) {
            $item->subtotal = $item->price * $item->quantity;
        });
        static::saved(fn($item)   => $item->booking->recalculateTotal());
        static::deleted(fn($item) => $item->booking->recalculateTotal());
    }
}

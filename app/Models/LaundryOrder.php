<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LaundryOrder extends Model
{
    protected $fillable = [
        'booking_id', 'weight', 'garment_count',
        'detergent_type', 'special_instructions', 'express_service',
    ];

    protected $casts = [
        'weight'          => 'decimal:2',
        'express_service' => 'boolean',
    ];

    public function booking(): BelongsTo  { return $this->belongsTo(Booking::class); }
    public function items(): HasMany      { return $this->hasMany(LaundryItem::class); }

    public function getTotalGarmentsAttribute(): int
    {
        return $this->items()->sum('quantity');
    }
}

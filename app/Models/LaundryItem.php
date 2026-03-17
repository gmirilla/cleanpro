<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaundryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'laundry_order_id',
        'garment_type',
        'quantity',
        'service_type',
        'unit_price',
        'subtotal',
        'status',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'subtotal'   => 'decimal:2',
    ];

    public static array $garmentTypes = [
        'shirt',
        'trouser',
        'dress',
        'bedsheet',
        'curtain',
        'others',
    ];

    public static array $statuses = [
        'received',
        'washing',
        'drying',
        'ironing',
        'ready',
        'delivered',
    ];

    /**
     * Default unit prices (₦) per garment type.
     * These are used when no explicit unit_price is provided.
     * Adjust these values to reflect your business pricing.
     */
    public static array $defaultPrices = [
        'shirt'     => 0.00,
        'trouser'   => 600.00,
        'dress'     => 800.00,
        'bedsheet'  => 1200.00,
        'curtain'   => 1500.00,
        'others'    => 400.00,
    ];

    /**
     * Get the default unit price for a given garment type.
     */
    public static function defaultPriceFor(string $garmentType): float
    {
        return self::$defaultPrices[$garmentType] ?? 400.00;
    }

    // ── Relationships ────────────────────────────────────────────

    public function laundryOrder(): BelongsTo
    {
        return $this->belongsTo(LaundryOrder::class);
    }

    // ── Auto-calculate subtotal on save ──────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (self $item) {
            // Ensure unit_price is set (fall back to default)
            if (!$item->unit_price || $item->unit_price <= 0) {
                $item->unit_price = self::defaultPriceFor($item->garment_type);
            }
            $item->subtotal = round($item->unit_price * $item->quantity, 2);
        });
    }

    // ── Accessors ────────────────────────────────────────────────

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

public static function activeGarmentTypes(): array
{
    return \App\Models\GarmentPrice::activeOptions(); // ['shirt' => 'Shirt', ...]
}
}

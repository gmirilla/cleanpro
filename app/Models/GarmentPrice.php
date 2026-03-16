<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GarmentPrice extends Model
{
    protected $fillable = [
        'garment_type',
        'label',
        'price',
        'is_active',
    ];

    protected $casts = [
        'price'     => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Return only active garment types (used in booking form dropdowns).
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Look up the price for a given garment_type slug.
     */
    public static function priceFor(string $garmentType): float
    {
        return (float) static::where('garment_type', $garmentType)->value('price') ?? 0.0;
    }

    /**
     * All active garment types keyed by slug — useful for dropdowns.
     * e.g. ['shirt' => 'Shirt', 'trouser' => 'Trouser']
     */
    public static function activeOptions(): array
    {
        return static::active()
            ->orderBy('label')
            ->pluck('label', 'garment_type')
            ->toArray();
    }
}

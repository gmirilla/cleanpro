<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Address extends Model
{
    use HasFactory;
    protected $fillable = [
        'customer_id', 'label', 'address', 'city', 'state', 'postal_code', 'is_default',
    ];

    protected $casts = ['is_default' => 'boolean'];

    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }

    public function getFullAddressAttribute(): string
    {
        return "{$this->address}, {$this->city}, {$this->state}";
    }
}

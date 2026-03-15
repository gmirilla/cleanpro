<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'name', 'category', 'description',
        'base_price', 'duration_minutes', 'status', 'sort_order',
    ];

    protected $casts = ['base_price' => 'decimal:2'];

    // ── Relationships ──────────────────────────────────────────
    public function bookingItems(): HasMany { return $this->hasMany(BookingItem::class); }

    // ── Scopes ─────────────────────────────────────────────────
    public function scopeActive($query)   { return $query->where('status', 'active'); }
    public function scopeCleaning($query) { return $query->where('category', 'cleaning'); }
    public function scopeLaundry($query)  { return $query->where('category', 'laundry'); }

    // ── Accessors ──────────────────────────────────────────────
    public function getDurationForHumansAttribute(): string
    {
        $h = intdiv($this->duration_minutes, 60);
        $m = $this->duration_minutes % 60;
        return $h > 0 ? "{$h}h {$m}m" : "{$m}m";
    }

    public function isActive(): bool { return $this->status === 'active'; }
}

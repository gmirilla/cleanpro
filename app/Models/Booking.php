<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Booking extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'customer_id', 'address_id', 'assigned_staff_id',
        'booking_reference', 'service_date', 'pickup_date', 'delivery_date',
        'status', 'total_amount', 'notes', 'cancellation_reason',
        'confirmed_at', 'completed_at',
    ];

    protected $casts = [
        'service_date'   => 'datetime',
        'pickup_date'    => 'datetime',
        'delivery_date'  => 'datetime',
        'confirmed_at'   => 'datetime',
        'completed_at'   => 'datetime',
        'total_amount'   => 'decimal:2',
    ];

    // ── Boot ───────────────────────────────────────────────────
    protected static function boot(): void
    {
        parent::boot();
        static::creating(fn($b) => $b->booking_reference ??= 'BK-' . strtoupper(Str::random(8)));
    }

    // ── Relationships ──────────────────────────────────────────
    public function customer(): BelongsTo    { return $this->belongsTo(Customer::class); }
    public function address(): BelongsTo     { return $this->belongsTo(Address::class); }
    public function assignedStaff(): BelongsTo { return $this->belongsTo(Staff::class, 'assigned_staff_id'); }
    public function items(): HasMany         { return $this->hasMany(BookingItem::class); }
    public function invoice(): HasOne        { return $this->hasOne(Invoice::class); }
    public function payment(): HasOne        { return $this->hasOne(Payment::class); }
    public function laundryOrder(): HasOne   { return $this->hasOne(LaundryOrder::class); }
    public function photos(): HasMany        { return $this->hasMany(JobPhoto::class); }
    public function reviews(): HasMany       { return $this->hasMany(StaffReview::class); }

    // ── Helpers ────────────────────────────────────────────────
    public function isPending(): bool    { return $this->status === 'pending'; }
    public function isCompleted(): bool  { return $this->status === 'completed'; }
    public function isCancelled(): bool  { return $this->status === 'cancelled'; }
    public function isInProgress(): bool { return $this->status === 'in_progress'; }

    public function hasLaundryItems(): bool
    {
        return $this->items()->whereHas('service', fn($q) => $q->where('category', 'laundry'))->exists();
    }

    public function recalculateTotal(): void
    {
        $this->update(['total_amount' => $this->items()->sum('subtotal')]);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending'     => 'yellow',
            'confirmed'   => 'blue',
            'assigned'    => 'indigo',
            'in_progress' => 'purple',
            'completed'   => 'green',
            'cancelled'   => 'red',
            default       => 'gray',
        };
    }
}

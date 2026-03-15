<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use SoftDeletes, HasFactory;


    protected $fillable = ['user_id', 'phone', 'notes'];

    // ── Relationships ──────────────────────────────────────────
    public function user(): BelongsTo    { return $this->belongsTo(User::class); }
    public function addresses(): HasMany { return $this->hasMany(Address::class); }
    public function bookings(): HasMany  { return $this->hasMany(Booking::class); }
    public function reviews(): HasMany   { return $this->hasMany(StaffReview::class); }

    // ── Helpers ────────────────────────────────────────────────
    public function defaultAddress(): ?Address
    {
        return $this->addresses()->where('is_default', true)->first()
            ?? $this->addresses()->first();
    }

    // ── Accessors (proxy to user) ──────────────────────────────
    public function getNameAttribute(): string  { return $this->user->name ?? ''; }
    public function getEmailAttribute(): string { return $this->user->email ?? ''; }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Staff extends Model
{
    use SoftDeletes, HasFactory;

    protected $table = 'staff';

    protected $fillable = [
        'user_id', 'phone', 'position',
        'availability_status', 'working_days', 'shift_start', 'shift_end',
        'rating', 'completed_jobs',
    ];

    protected $casts = [
        'working_days' => 'array',
        'rating'       => 'decimal:2',
    ];

    // ── Relationships ──────────────────────────────────────────
    public function user(): BelongsTo    { return $this->belongsTo(User::class); }
    public function bookings(): HasMany  { return $this->hasMany(Booking::class, 'assigned_staff_id'); }
    public function reviews(): HasMany   { return $this->hasMany(StaffReview::class); }

    // ── Scopes ─────────────────────────────────────────────────
    public function scopeAvailable($q) { return $q->where('availability_status', 'available'); }

    // ── Helpers ────────────────────────────────────────────────
    public function isAvailable(): bool { return $this->availability_status === 'available'; }
    public function getNameAttribute(): string { return $this->user->name ?? ''; }

    public function recalculateRating(): void
    {
        $avg   = $this->reviews()->avg('rating') ?? 0;
        $count = $this->bookings()->where('status', 'completed')->count();
        $this->update(['rating' => round($avg, 2), 'completed_jobs' => $count]);
    }
}

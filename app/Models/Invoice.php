<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Invoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'booking_id', 'invoice_number',
        'amount', 'tax', 'discount', 'total',
        'status', 'due_date', 'paid_at', 'notes',
    ];

    protected $casts = [
        'amount'   => 'decimal:2',
        'tax'      => 'decimal:2',
        'discount' => 'decimal:2',
        'total'    => 'decimal:2',
        'due_date' => 'date',
        'paid_at'  => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (self $invoice) {
            $invoice->invoice_number ??= 'INV-' . date('Y') . '-' . strtoupper(Str::random(6));
        });
    }

    // ── Relationships ──────────────────────────────────────────
    public function booking(): BelongsTo { return $this->belongsTo(Booking::class); }
    public function payment(): HasOne    { return $this->hasOne(Payment::class); }

    // ── Helpers ────────────────────────────────────────────────
    public function isPaid(): bool    { return $this->status === 'paid'; }
    public function isOverdue(): bool { return !$this->isPaid() && $this->due_date->isPast(); }

    public function markPaid(): void
    {
        $this->update(['status' => 'paid', 'paid_at' => now()]);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft'     => 'gray',
            'unpaid'    => 'yellow',
            'paid'      => 'green',
            'cancelled' => 'red',
            default     => 'gray',
        };
    }
}

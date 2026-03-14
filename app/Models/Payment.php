<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'booking_id', 'invoice_id', 'amount', 'currency',
        'payment_method', 'payment_status', 'transaction_reference',
        'gateway_reference', 'gateway_response', 'paid_at',
    ];

    protected $casts = [
        'amount'           => 'decimal:2',
        'gateway_response' => 'array',
        'paid_at'          => 'datetime',
    ];

    public function booking(): BelongsTo { return $this->belongsTo(Booking::class); }
    public function invoice(): BelongsTo { return $this->belongsTo(Invoice::class); }

    public function isCompleted(): bool { return $this->payment_status === 'completed'; }
}

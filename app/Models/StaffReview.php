<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffReview extends Model
{
    protected $fillable = ['booking_id', 'staff_id', 'customer_id', 'rating', 'comment'];

    public function booking(): BelongsTo  { return $this->belongsTo(Booking::class); }
    public function staff(): BelongsTo    { return $this->belongsTo(Staff::class); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }

    protected static function boot(): void
    {
        parent::boot();
        static::saved(fn($r) => $r->staff->recalculateRating());
    }
}

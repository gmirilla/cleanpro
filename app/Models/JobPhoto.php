<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;


class JobPhoto extends Model
{
    protected $fillable = ['booking_id', 'uploaded_by', 'path', 'type', 'caption'];

    public function booking(): BelongsTo  { return $this->belongsTo(Booking::class); }
    public function uploader(): BelongsTo { return $this->belongsTo(User::class, 'uploaded_by'); }

    public function getUrlAttribute(): string
    {
        return Storage::url($this->path);
    }
}

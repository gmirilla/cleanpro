<?php

namespace App\Events;

use App\Models\Booking;
use App\Models\Staff;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StaffAssigned
{
    use Dispatchable, SerializesModels;
    public function __construct(
        public readonly Booking $booking,
        public readonly Staff   $staff
    ) {}
}

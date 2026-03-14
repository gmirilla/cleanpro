<?php

namespace App\Listeners;

use App\Events\BookingStatusChanged;

class ReleaseStaffOnCompletion
{
    public function handle(BookingStatusChanged $event): void
    {
        if (!in_array($event->newStatus, ['completed', 'cancelled'])) return;

        $staff = $event->booking->assignedStaff;
        if ($staff) {
            $staff->update(['availability_status' => 'available']);
            $staff->recalculateRating();
        }
    }
}

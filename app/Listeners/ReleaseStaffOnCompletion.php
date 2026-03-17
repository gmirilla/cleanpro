<?php

namespace App\Listeners;

use App\Events\BookingStatusChanged;
use App\Services\DashboardService;

class ReleaseStaffOnCompletion
{
    public function handle(BookingStatusChanged $event): void
    {
        if (!in_array($event->newStatus, ['completed', 'cancelled'])) return;

        $booking = $event->booking;

        if ($booking->assignedStaff) {
            $booking->assignedStaff->update(['availability_status' => 'available']);
            $booking->assignedStaff->recalculateRating();

            // Clear cached stats for this staff member
            DashboardService::clearCache(staffId: $booking->assignedStaff->id);
        }

        // Clear admin + customer dashboard caches
        DashboardService::clearCache(customerId: $booking->customer_id);
    }
}

<?php

namespace App\Listeners;

use App\Events\PaymentCompleted;
use App\Services\DashboardService;

class UpdateBookingOnPayment
{
    public function handle(PaymentCompleted $event): void
    {
        $booking = $event->payment->booking;

        if ($booking->isPending()) {
            $booking->update([
                'status'       => 'confirmed',
                'confirmed_at' => now(),
            ]);
        }

        // Bust dashboard caches so revenue totals update immediately
        DashboardService::clearCache(
            staffId:    $booking->assigned_staff_id,
            customerId: $booking->customer_id
        );
    }
}

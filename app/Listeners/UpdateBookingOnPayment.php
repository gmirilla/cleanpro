<?php

namespace App\Listeners;

use App\Events\PaymentCompleted;

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
    }
}

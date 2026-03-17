<?php

namespace App\Listeners;

use App\Events\BookingCreated;
use App\Notifications\BookingConfirmedNotification;
use App\Services\DashboardService;

class SendBookingConfirmation
{
    public function handle(BookingCreated $event): void
    {
        $event->booking->customer->user->notify(
            new BookingConfirmedNotification($event->booking)
        );

        // Bust admin dashboard cache so new booking shows immediately
        DashboardService::clearCache();
    }
}

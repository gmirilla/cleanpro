<?php

namespace App\Listeners;

use App\Events\BookingCreated;
use App\Notifications\BookingConfirmedNotification;

class SendBookingConfirmation
{
    public function handle(BookingCreated $event): void
    {
        $event->booking->customer->user->notify(
            new BookingConfirmedNotification($event->booking)
        );
    }
}

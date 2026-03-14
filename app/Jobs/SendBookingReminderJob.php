<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Notifications\BookingReminderNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendBookingReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public function __construct(public readonly Booking $booking) {}

    public function handle(): void
    {
        $booking = Booking::find($this->booking->id);

        if (!$booking || $booking->isCancelled()) return;

        $booking->customer->user->notify(
            new BookingReminderNotification($booking)
        );
    }
}

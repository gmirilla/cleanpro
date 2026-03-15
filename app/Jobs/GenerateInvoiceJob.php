<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Services\InvoiceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateInvoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public readonly Booking $booking) {}

    public function handle(InvoiceService $invoiceService): void
    {
        $booking = Booking::with('invoice')->find($this->booking->id);

        if (!$booking) return;

        // Don't create a duplicate
        if ($booking->invoice) return;

        // Only generate for confirmed or completed bookings
        if (!in_array($booking->status, ['confirmed', 'assigned', 'in_progress', 'completed'])) {
            return;
        }

        $invoiceService->generateFromBooking($booking);
    }
}

<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AssignStaffJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 60;

    public function __construct(public readonly Booking $booking) {}

    public function handle(BookingService $bookingService): void
    {
        // Re-fetch to ensure freshness
        $booking = Booking::find($this->booking->id);

        if (!$booking || $booking->assigned_staff_id || $booking->isCancelled()) {
            return;
        }

        $staff = $bookingService->findBestAvailableStaff($booking);

        if ($staff) {
            $bookingService->assignStaff($booking, $staff);
            Log::info("Auto-assigned staff #{$staff->id} to booking #{$booking->id}");
        } else {
            Log::warning("No available staff for booking #{$booking->id}");
        }
    }
}

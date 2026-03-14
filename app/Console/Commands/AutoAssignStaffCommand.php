<?php

namespace App\Console\Commands;

use App\Jobs\AssignStaffJob;
use App\Models\Booking;
use Illuminate\Console\Command;

class AutoAssignStaffCommand extends Command
{
    protected $signature   = 'bookings:auto-assign';
    protected $description = 'Auto-assign available staff to unassigned pending bookings';

    public function handle(): int
    {
        $bookings = Booking::where('status', 'pending')
            ->whereNull('assigned_staff_id')
            ->where('service_date', '>', now())
            ->get();

        if ($bookings->isEmpty()) {
            $this->info('No unassigned bookings found.');
            return self::SUCCESS;
        }

        $this->info("Found {$bookings->count()} unassigned booking(s). Dispatching assignment jobs…");

        foreach ($bookings as $booking) {
            AssignStaffJob::dispatch($booking);
        }

        $this->info('Done.');
        return self::SUCCESS;
    }
}

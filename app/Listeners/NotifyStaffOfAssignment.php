<?php

namespace App\Listeners;

use App\Events\BookingStatusChanged;
use App\Notifications\JobAssignedNotification;

class NotifyStaffOfAssignment
{
    public function handle(BookingStatusChanged $event): void
    {
        if ($event->newStatus !== 'assigned') return;

        $staff = $event->booking->assignedStaff;
        if ($staff) {
            $staff->user->notify(new JobAssignedNotification($event->booking));
        }
    }
}

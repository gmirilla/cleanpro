<?php

namespace App\Providers;

use App\Events\BookingCreated;
use App\Events\BookingStatusChanged;
use App\Events\JobCompleted;
use App\Events\PaymentCompleted;
use App\Events\StaffAssigned;
use App\Listeners\NotifyStaffOfAssignment;
use App\Listeners\ReleaseStaffOnCompletion;
use App\Listeners\SendBookingConfirmation;
use App\Listeners\SendPaymentReceipt;
use App\Listeners\UpdateBookingOnPayment;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        BookingCreated::class => [
            SendBookingConfirmation::class,
        ],
        BookingStatusChanged::class => [
            NotifyStaffOfAssignment::class,
            ReleaseStaffOnCompletion::class,
        ],
        PaymentCompleted::class => [
            SendPaymentReceipt::class,
            UpdateBookingOnPayment::class,
        ],
    ];

    public function boot(): void {}
}

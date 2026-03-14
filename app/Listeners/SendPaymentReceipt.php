<?php

namespace App\Listeners;

use App\Events\PaymentCompleted;
use App\Notifications\PaymentReceiptNotification;

class SendPaymentReceipt
{
    public function handle(PaymentCompleted $event): void
    {
        $payment  = $event->payment;
        $customer = $payment->booking->customer;
        $customer->user->notify(new PaymentReceiptNotification($payment));
    }
}

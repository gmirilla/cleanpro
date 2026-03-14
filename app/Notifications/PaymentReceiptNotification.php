<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentReceiptNotification extends Notification
{
    public function __construct(public readonly Payment $payment) {}

    public function via($notifiable): array { return ['mail', 'database']; }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Payment Received ✓')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('We have received your payment. Thank you!')
            ->line('**Amount Paid:** ₦' . number_format($this->payment->amount, 2))
            ->line('**Reference:** ' . $this->payment->transaction_reference)
            ->line('**Date:** ' . $this->payment->paid_at?->format('d M Y h:i A'))
            ->action('View Receipt', url('/customer/payments/' . $this->payment->id));
    }

    public function toArray($notifiable): array
    {
        return [
            'type'       => 'payment_received',
            'payment_id' => $this->payment->id,
            'message'    => 'Payment of ₦' . number_format($this->payment->amount, 2) . ' received.',
        ];
    }
}

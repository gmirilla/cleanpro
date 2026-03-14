<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class JobCompletedNotification extends Notification
{
    public function __construct(public readonly Booking $booking) {}

    public function via($notifiable): array { return ['mail', 'database']; }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Job Completed – ' . $this->booking->booking_reference)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your service has been completed successfully.')
            ->line('**Reference:** ' . $this->booking->booking_reference)
            ->line('**Completed:** ' . $this->booking->completed_at?->format('d M Y h:i A'))
            ->when($this->booking->invoice, fn($mail) =>
                $mail->action('View Invoice', url('/customer/invoices/' . $this->booking->invoice->id))
            );
    }

    public function toArray($notifiable): array
    {
        return [
            'type'       => 'job_completed',
            'booking_id' => $this->booking->id,
            'message'    => 'Job ' . $this->booking->booking_reference . ' completed.',
        ];
    }
}

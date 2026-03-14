<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingReminderNotification extends Notification
{
    public function __construct(public readonly Booking $booking) {}

    public function via($notifiable): array { return ['mail', 'database']; }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Reminder: Your Booking is Tomorrow')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('This is a friendly reminder about your upcoming booking.')
            ->line('**Reference:** ' . $this->booking->booking_reference)
            ->line('**Date:** ' . $this->booking->service_date->format('D, d M Y h:i A'))
            ->when($this->booking->assignedStaff, fn($mail) =>
                $mail->line('**Assigned Staff:** ' . $this->booking->assignedStaff->name)
            )
            ->action('View Details', url('/customer/bookings/' . $this->booking->id));
    }

    public function toArray($notifiable): array
    {
        return [
            'type'       => 'booking_reminder',
            'booking_id' => $this->booking->id,
            'message'    => 'Reminder: Booking ' . $this->booking->booking_reference . ' is tomorrow.',
        ];
    }
}

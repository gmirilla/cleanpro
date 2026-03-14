<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingConfirmedNotification extends Notification
{
    public function __construct(public readonly Booking $booking) {}

    public function via($notifiable): array { return ['mail', 'database']; }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Booking Confirmed – ' . $this->booking->booking_reference)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your booking has been received and confirmed.')
            ->line('**Reference:** ' . $this->booking->booking_reference)
            ->line('**Service Date:** ' . $this->booking->service_date->format('D, d M Y h:i A'))
            ->line('**Total:** ₦' . number_format($this->booking->total_amount, 2))
            ->action('View Booking', url('/customer/bookings/' . $this->booking->id))
            ->line('Thank you for choosing us!');
    }

    public function toArray($notifiable): array
    {
        return [
            'type'       => 'booking_confirmed',
            'booking_id' => $this->booking->id,
            'reference'  => $this->booking->booking_reference,
            'message'    => 'Your booking ' . $this->booking->booking_reference . ' has been confirmed.',
        ];
    }
}

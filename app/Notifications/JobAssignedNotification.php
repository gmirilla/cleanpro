<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class JobAssignedNotification extends Notification
{
    public function __construct(public readonly Booking $booking) {}

    public function via($notifiable): array { return ['mail', 'database']; }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Job Assigned: ' . $this->booking->booking_reference)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A new job has been assigned to you.')
            ->line('**Reference:** ' . $this->booking->booking_reference)
            ->line('**Date:** ' . $this->booking->service_date->format('D, d M Y h:i A'))
            ->line('**Address:** ' . ($this->booking->address?->full_address ?? 'To be confirmed'))
            ->line('**Services:** ' . $this->booking->items->pluck('service.name')->join(', '))
            ->action('View Job', url('/staff/bookings/' . $this->booking->id));
    }

    public function toArray($notifiable): array
    {
        return [
            'type'       => 'job_assigned',
            'booking_id' => $this->booking->id,
            'message'    => 'Job ' . $this->booking->booking_reference . ' has been assigned to you.',
        ];
    }
}

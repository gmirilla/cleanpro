<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceReadyNotification extends Notification
{
    public function __construct(public readonly Invoice $invoice) {}

    public function via($notifiable): array { return ['mail', 'database']; }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Invoice Ready – ' . $this->invoice->invoice_number)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your invoice is ready for payment.')
            ->line('**Invoice:** ' . $this->invoice->invoice_number)
            ->line('**Amount Due:** ₦' . number_format($this->invoice->total, 2))
            ->line('**Due Date:** ' . $this->invoice->due_date->format('d M Y'))
            ->action('Pay Now', url('/customer/invoices/' . $this->invoice->id));
    }

    public function toArray($notifiable): array
    {
        return [
            'type'       => 'invoice_ready',
            'invoice_id' => $this->invoice->id,
            'message'    => 'Invoice ' . $this->invoice->invoice_number . ' is ready for payment.',
        ];
    }
}

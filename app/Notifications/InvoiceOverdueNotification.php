<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceOverdueNotification extends Notification
{
    public function __construct(public readonly Invoice $invoice) {}

    public function via($notifiable): array { return ['mail', 'database']; }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('⚠️ Overdue Invoice – ' . $this->invoice->invoice_number)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your invoice is overdue. Please make payment as soon as possible.')
            ->line('**Invoice:** ' . $this->invoice->invoice_number)
            ->line('**Amount Due:** ₦' . number_format($this->invoice->total, 2))
            ->line('**Was Due:** ' . $this->invoice->due_date->format('d M Y'))
            ->action('Pay Now', url('/customer/invoices/' . $this->invoice->id));
    }

    public function toArray($notifiable): array
    {
        return [
            'type'       => 'invoice_overdue',
            'invoice_id' => $this->invoice->id,
            'message'    => 'Invoice ' . $this->invoice->invoice_number . ' is overdue.',
        ];
    }
}

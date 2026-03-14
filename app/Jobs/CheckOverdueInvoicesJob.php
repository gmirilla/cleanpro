<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Notifications\InvoiceOverdueNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckOverdueInvoicesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        Invoice::where('status', 'unpaid')
            ->where('due_date', '<', now())
            ->with('booking.customer.user')
            ->chunk(100, function ($invoices) {
                foreach ($invoices as $invoice) {
                    $invoice->booking->customer->user->notify(
                        new InvoiceOverdueNotification($invoice)
                    );
                }
            });
    }
}

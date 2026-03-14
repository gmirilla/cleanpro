<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Invoice;
use App\Notifications\InvoiceReadyNotification;

class InvoiceService
{
    private float $taxRate = 0.075; // 7.5% VAT

    public function generateFromBooking(Booking $booking): Invoice
    {
        if ($existing = Invoice::where('booking_id', $booking->id)->first()) {
            return $existing;
        }

        $amount   = $booking->total_amount;
        $tax      = round($amount * $this->taxRate, 2);
        $total    = $amount + $tax;

        $invoice = Invoice::create([
            'booking_id' => $booking->id,
            'amount'     => $amount,
            'tax'        => $tax,
            'discount'   => 0,
            'total'      => $total,
            'status'     => 'unpaid',
            'due_date'   => now()->addDays(7),
        ]);

        $booking->customer->user->notify(new InvoiceReadyNotification($invoice));

        return $invoice;
    }

    public function applyDiscount(Invoice $invoice, float $discount): void
    {
        $invoice->update([
            'discount' => $discount,
            'total'    => max(0, $invoice->amount + $invoice->tax - $discount),
        ]);
    }

    public function markOverdue(): int
    {
        return Invoice::query()
            ->where('status', 'unpaid')
            ->where('due_date', '<', now())
            ->update(['status' => 'unpaid']); // Could be a separate 'overdue' status
    }
}

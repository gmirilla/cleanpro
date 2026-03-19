<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Invoice;
use App\Notifications\InvoiceReadyNotification;

class InvoiceService
{
    /**
     * Whether VAT is enabled (driven by config/cleanpro.php → VAT_ENABLED env var).
     */
    private bool $vatEnabled;

    /**
     * VAT rate as a decimal, e.g. 0.075 for 7.5 %.
     * Only used when $vatEnabled is true.
     */
    private float $taxRate;

    public function __construct()
    {
        $this->vatEnabled = (bool) config('cleanpro.vat_enabled', true);
        $this->taxRate    = (float) config('cleanpro.vat_rate', 0.075);
    }

    // ─────────────────────────────────────────────────────────────

    public function generateFromBooking(Booking $booking): Invoice
    {
        if ($existing = Invoice::where('booking_id', $booking->id)->first()) {
            return $existing;
        }

        $amount = (float) $booking->total_amount;
        $tax    = $this->vatEnabled ? round($amount * $this->taxRate, 2) : 0.00;
        $total  = $amount + $tax;

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
            ->count(); // Returns count only; status update handled elsewhere
    }

    // ─────────────────────────────────────────────────────────────
    // Helpers used by views / other services
    // ─────────────────────────────────────────────────────────────

    /**
     * Returns true when VAT is configured and enabled.
     */
    public static function vatEnabled(): bool
    {
        return (bool) config('cleanpro.vat_enabled', true);
    }

    /**
     * Human-readable label for the VAT line, e.g. "VAT (7.5%)".
     */
    public static function vatLabel(): string
    {
        return (string) config('cleanpro.vat_label', 'VAT (7.5%)');
    }

    /**
     * Calculate tax for a given amount according to current config.
     * Returns 0 when VAT is disabled.
     */
    public static function calculateTax(float $amount): float
    {
        if (! config('cleanpro.vat_enabled', true)) {
            return 0.00;
        }

        return round($amount * (float) config('cleanpro.vat_rate', 0.075), 2);
    }
}

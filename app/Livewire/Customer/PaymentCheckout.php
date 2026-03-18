<?php

namespace App\Livewire\Customer;

use App\Models\Invoice;
use App\Services\PaymentService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.customer')]
#[Title('Pay Invoice')]
class PaymentCheckout extends Component
{
    // Store the ID, not the model — Livewire serialises public properties
    // between requests; Eloquent models must be re-fetched each time
    public int    $invoiceId;
    public string $gateway      = 'paystack';
    public string $clientSecret = '';
    public bool   $processing   = false;
    public bool   $paid         = false;

    /**
     * Route: /customer/checkout/{invoice}
     * Livewire matches the route segment name → parameter name.
     * We accept Invoice via model binding so Laravel resolves it,
     * then we store only the ID.
     */
    public function mount(Invoice $invoice): void
    {
        $invoice->loadMissing('booking.customer');

        $booking = $invoice->booking;

        abort_unless($booking, 403, 'Booking not found.');
        abort_unless($booking->customer, 403, 'Customer profile not found.');
        abort_unless(
            $booking->customer->user_id === auth()->id(),
            403,
            'You are not authorised to pay this invoice.'
        );

        abort_if($invoice->isPaid(), 400, 'This invoice has already been paid.');

        $this->invoiceId = $invoice->id;
        $this->gateway   = config('services.default_gateway', 'paystack');
    }

    // Re-fetch the invoice fresh on every request
    private function getInvoice(): Invoice
    {
        return Invoice::with('booking.items.service', 'booking.customer.user', 'payment')
            ->findOrFail($this->invoiceId);
    }

    public function initializeStripe(PaymentService $paymentService): void
    {
        $this->processing = true;

        try {
            $data = $paymentService->createStripeIntent($this->getInvoice());
            $this->clientSecret = $data['client_secret'];
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Stripe init failed: ' . $e->getMessage());
        }

        $this->processing = false;
    }

    public function payWithPaystack(PaymentService $paymentService): void
    {
        $this->processing = true;

        try {
            $data = $paymentService->initializePaystack(
                $this->getInvoice(),
                auth()->user()->email
            );
            $this->redirect($data['authorization_url']);
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Payment failed: ' . $e->getMessage());
            $this->processing = false;
        }
    }

    public function render()
    {
        $invoice = $this->getInvoice();

        return view('livewire.customer.payment-checkout', compact('invoice'));
    }
}

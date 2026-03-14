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
    public Invoice $invoice;
    public string  $gateway='paystack';
    public string  $clientSecret='';
    public bool    $processing=false;
    public bool    $paid=false;

    public function mount(int $invoiceId): void
    {
        $invoice=Invoice::with('booking.customer.user')->findOrFail($invoiceId);
        abort_unless($invoice->booking->customer->user_id===auth()->id(),403);
        abort_if($invoice->isPaid(),400,'Invoice already paid.');
        $this->invoice=$invoice;
        $this->gateway=config('services.default_gateway','paystack');
    }

    public function initializeStripe(PaymentService $paymentService): void
    {
        $this->processing=true;
        $data=$paymentService->createStripeIntent($this->invoice);
        $this->clientSecret=$data['client_secret'];
        $this->processing=false;
    }

    public function payWithPaystack(PaymentService $paymentService): void
    {
        $this->processing=true;
        try {
            $data=$paymentService->initializePaystack($this->invoice,auth()->user()->email);
            $this->redirect($data['authorization_url']);
        } catch (\Exception $e) {
            $this->dispatch('notify',type:'error',message:'Payment failed: '.$e->getMessage());
            $this->processing=false;
        }
    }

    public function render()
    {
        return view('livewire.customer.payment-checkout');
    }
}

<?php

namespace App\Services;

use App\Events\PaymentCompleted;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Webhook as StripeWebhook;

class PaymentService
{
    // ── Paystack ───────────────────────────────────────────────

    public function initializePaystack(Invoice $invoice, string $email): array
    {
        $reference = 'PAY-' . strtoupper(uniqid());

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.paystack.secret_key'),
            'Content-Type'  => 'application/json',
        ])->post('https://api.paystack.co/transaction/initialize', [
            'email'     => $email,
            'amount'    => (int)($invoice->total * 100), // kobo
            'reference' => $reference,
            'metadata'  => [
                'invoice_id' => $invoice->id,
                'booking_id' => $invoice->booking_id,
            ],
            'callback_url' => route('payments.paystack.callback'),
        ]);

        if (!$response->successful() || !$response->json('status')) {
            throw new \RuntimeException('Paystack initialization failed: ' . $response->json('message'));
        }

        Payment::create([
            'booking_id'            => $invoice->booking_id,
            'invoice_id'            => $invoice->id,
            'amount'                => $invoice->total,
            'currency'              => 'NGN',
            'payment_method'        => 'paystack',
            'payment_status'        => 'pending',
            'transaction_reference' => $reference,
        ]);

        return $response->json('data');
    }

    public function verifyPaystack(string $reference): bool
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.paystack.secret_key'),
        ])->get("https://api.paystack.co/transaction/verify/{$reference}");

        if (!$response->successful()) return false;

        $data = $response->json('data');

        if ($data['status'] !== 'success') return false;

        $payment = Payment::where('transaction_reference', $reference)->first();
        if (!$payment) return false;

        $this->completePayment($payment, $data['reference'], $data);
        return true;
    }

    public function handlePaystackWebhook(string $payload, string $signature): void
    {
        $computedSig = hash_hmac('sha512', $payload, config('services.paystack.secret_key'));

        if (!hash_equals($computedSig, $signature)) {
            Log::warning('Invalid Paystack webhook signature');
            return;
        }

        $event = json_decode($payload, true);

        if ($event['event'] === 'charge.success') {
            $reference = $event['data']['reference'];
            $payment   = Payment::where('transaction_reference', $reference)->first();
            if ($payment) {
                $this->completePayment($payment, $event['data']['reference'], $event['data']);
            }
        }
    }

    // ── Stripe ─────────────────────────────────────────────────

    public function createStripeIntent(Invoice $invoice): array
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $intent = PaymentIntent::create([
            'amount'   => (int)($invoice->total * 100),
            'currency' => 'usd',
            'metadata' => ['invoice_id' => $invoice->id, 'booking_id' => $invoice->booking_id],
        ]);

        Payment::create([
            'booking_id'           => $invoice->booking_id,
            'invoice_id'           => $invoice->id,
            'amount'               => $invoice->total,
            'currency'             => 'USD',
            'payment_method'       => 'stripe',
            'payment_status'       => 'pending',
            'gateway_reference'    => $intent->id,
        ]);

        return ['client_secret' => $intent->client_secret, 'id' => $intent->id];
    }

    public function handleStripeWebhook(string $payload, string $signature): void
    {
        try {
            $event = StripeWebhook::constructEvent(
                $payload,
                $signature,
                config('services.stripe.webhook_secret')
            );
        } catch (\Exception $e) {
            Log::warning('Stripe webhook error: ' . $e->getMessage());
            return;
        }

        if ($event->type === 'payment_intent.succeeded') {
            $intent  = $event->data->object;
            $payment = Payment::where('gateway_reference', $intent->id)->first();
            if ($payment) {
                $this->completePayment($payment, $intent->id, ['stripe_id' => $intent->id]);
            }
        }
    }

    // ── Cash ───────────────────────────────────────────────────

    public function recordCashPayment(Invoice $invoice): Payment
    {
        $payment = Payment::create([
            'booking_id'            => $invoice->booking_id,
            'invoice_id'            => $invoice->id,
            'amount'                => $invoice->total,
            'currency'              => 'NGN',
            'payment_method'        => 'cash',
            'payment_status'        => 'completed',
            'transaction_reference' => 'CASH-' . strtoupper(uniqid()),
            'paid_at'               => now(),
        ]);

        $invoice->markPaid();
        event(new PaymentCompleted($payment));
        return $payment;
    }

    // ── Shared completion ──────────────────────────────────────

    private function completePayment(Payment $payment, string $gatewayRef, array $response): void
    {
        $payment->update([
            'payment_status'        => 'completed',
            'gateway_reference'     => $gatewayRef,
            'gateway_response'      => $response,
            'paid_at'               => now(),
        ]);

        $payment->invoice->markPaid();

        event(new PaymentCompleted($payment));
    }
}

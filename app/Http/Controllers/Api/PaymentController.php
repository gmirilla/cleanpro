<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Jobs\ProcessPaystackWebhookJob;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $paymentService) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Payment::class);
        return PaymentResource::collection(
            Payment::with('booking.customer.user')->latest()->paginate(15)
        );
    }

    public function show(Payment $payment): PaymentResource
    {
        $this->authorize('view', $payment);
        return new PaymentResource($payment->load('booking', 'invoice'));
    }

    public function verify(Request $request, string $reference): JsonResponse
    {
        $success = $this->paymentService->verifyPaystack($reference);
        return response()->json([
            'verified' => $success,
            'message'  => $success ? 'Payment verified.' : 'Verification failed.',
        ]);
    }

    public function paystackWebhook(Request $request): JsonResponse
    {
        $payload   = $request->getContent();
        $signature = $request->header('x-paystack-signature', '');

        ProcessPaystackWebhookJob::dispatch($payload, $signature);

        return response()->json(['status' => 'received']);
    }

    public function stripeWebhook(Request $request): JsonResponse
    {
        $payload   = $request->getContent();
        $signature = $request->header('Stripe-Signature', '');

        \App\Jobs\ProcessStripeWebhookJob::dispatch($payload, $signature);

        return response()->json(['status' => 'received']);
    }
}

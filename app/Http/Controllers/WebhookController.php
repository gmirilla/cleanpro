<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessPaystackWebhookJob;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class WebhookController extends Controller
{
    public function paystack(Request $request): Response
    {
        $payload   = $request->getContent();
        $signature = $request->header('x-paystack-signature', '');

        ProcessPaystackWebhookJob::dispatch($payload, $signature);

        return response('OK', 200);
    }

    public function stripe(Request $request): Response
    {
        $payload   = $request->getContent();
        $signature = $request->header('Stripe-Signature', '');

        \App\Jobs\ProcessStripeWebhookJob::dispatch($payload, $signature);

        return response('OK', 200);
    }
}

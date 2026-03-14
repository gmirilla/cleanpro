<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaystackController extends Controller
{
    public function callback(Request $request, PaymentService $paymentService)
    {
        $reference = $request->query('reference');

        if (!$reference) {
            return redirect()->route('customer.invoices')
                ->with('error', 'Invalid payment reference.');
        }

        $success = $paymentService->verifyPaystack($reference);

        if ($success) {
            return redirect()->route('customer.dashboard')
                ->with('success', 'Payment successful! Your booking is confirmed.');
        }

        return redirect()->route('customer.invoices')
            ->with('error', 'Payment verification failed. Please contact support.');
    }
}

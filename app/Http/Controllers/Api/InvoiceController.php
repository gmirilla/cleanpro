<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::query()->with('booking.customer.user');

        if ($request->user()->isCustomer()) {
            $customerId = $request->user()->customer?->id;
            $query->whereHas('booking', fn($q) => $q->where('customer_id', $customerId));
        }

        return InvoiceResource::collection($query->latest()->paginate(15));
    }

    public function show(Invoice $invoice): InvoiceResource
    {
        $this->authorize('view', $invoice);
        return new InvoiceResource($invoice->load('booking.items.service', 'payment'));
    }

    public function initiatePayment(Request $request, Invoice $invoice, PaymentService $paymentService): JsonResponse
    {
        $this->authorize('view', $invoice);
        abort_if($invoice->isPaid(), 400, 'Invoice already paid.');

        $data = $paymentService->initializePaystack($invoice, $request->user()->email);
        return response()->json(['payment_url' => $data['authorization_url'], 'reference' => $data['reference']]);
    }
}

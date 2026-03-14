<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $customer = $request->user()->customer;
        abort_unless($customer, 404);

        $invoices = Invoice::whereHas('booking', fn($q) => $q->where('customer_id', $customer->id))
            ->with(['booking.items.service'])
            ->latest()
            ->paginate(10);

        return view('customer.invoices.index', compact('invoices'));
    }

    public function show(Invoice $invoice, Request $request)
    {
        $customer = $request->user()->customer;
        abort_unless($customer && $invoice->booking->customer_id === $customer->id, 403);

        $invoice->load(['booking.items.service', 'booking.assignedStaff.user', 'payment']);
        return view('customer.invoices.show', compact('invoice'));
    }
}

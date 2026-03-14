@extends('layouts.customer')

@section('content')
<div class="max-w-2xl mx-auto">
    <a href="{{ route('customer.invoices') }}" class="text-sm text-gray-400 hover:text-gray-600 mb-4 inline-block">← Back to Invoices</a>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        {{-- Header --}}
        <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 p-6 text-white">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-indigo-200 text-sm">Invoice</p>
                    <h1 class="text-2xl font-bold">{{ $invoice->invoice_number }}</h1>
                    <p class="text-indigo-200 text-sm mt-1">Booking: {{ $invoice->booking->booking_reference }}</p>
                </div>
                <span class="px-3 py-1 rounded-full text-sm font-semibold
                    {{ $invoice->isPaid() ? 'bg-green-400 text-green-900' : 'bg-yellow-400 text-yellow-900' }}">
                    {{ ucfirst($invoice->status) }}
                </span>
            </div>
        </div>

        <div class="p-6">
            {{-- Service Date --}}
            <div class="mb-5 text-sm text-gray-600">
                <span>📅 Service Date: </span>
                <span class="font-medium">{{ $invoice->booking->service_date->format('D, d M Y h:i A') }}</span>
            </div>

            {{-- Items --}}
            <div class="space-y-2 border-t border-b border-gray-100 py-4 mb-4">
                @foreach($invoice->booking->items as $item)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-700">{{ $item->service->name }}
                            @if($item->quantity > 1)<span class="text-gray-400 text-xs">× {{ $item->quantity }}</span>@endif
                        </span>
                        <span class="font-medium text-gray-800">₦{{ number_format($item->subtotal, 2) }}</span>
                    </div>
                @endforeach
            </div>

            {{-- Totals --}}
            <div class="space-y-2 text-sm mb-6">
                <div class="flex justify-between text-gray-500">
                    <span>Subtotal</span><span>₦{{ number_format($invoice->amount, 2) }}</span>
                </div>
                <div class="flex justify-between text-gray-500">
                    <span>VAT (7.5%)</span><span>₦{{ number_format($invoice->tax, 2) }}</span>
                </div>
                @if($invoice->discount > 0)
                    <div class="flex justify-between text-green-600">
                        <span>Discount</span><span>- ₦{{ number_format($invoice->discount, 2) }}</span>
                    </div>
                @endif
                <div class="flex justify-between font-bold text-gray-800 text-base border-t border-gray-200 pt-3 mt-2">
                    <span>Total</span><span>₦{{ number_format($invoice->total, 2) }}</span>
                </div>
            </div>

            {{-- Due date --}}
            <p class="text-sm text-gray-500 mb-4">
                Due: <span class="font-medium {{ $invoice->isOverdue() ? 'text-red-500' : 'text-gray-700' }}">
                    {{ $invoice->due_date->format('d M Y') }}
                    @if($invoice->isOverdue()) (Overdue) @endif
                </span>
            </p>

            {{-- Payment info or Pay button --}}
            @if($invoice->isPaid() && $invoice->payment)
                <div class="bg-green-50 border border-green-100 rounded-xl p-4 text-sm">
                    <p class="font-semibold text-green-700 mb-1">✓ Payment Received</p>
                    <p class="text-green-600">Paid on {{ $invoice->paid_at->format('d M Y h:i A') }}</p>
                    <p class="text-green-600">Ref: {{ $invoice->payment->transaction_reference }}</p>
                    <p class="text-green-600">Method: {{ ucfirst($invoice->payment->payment_method) }}</p>
                </div>
            @elseif(!$invoice->isPaid())
                <a href="{{ route('customer.checkout', $invoice->id) }}"
                   class="block text-center w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-xl font-semibold transition">
                    Pay ₦{{ number_format($invoice->total, 2) }} Now
                </a>
            @endif
        </div>
    </div>
</div>
@endsection

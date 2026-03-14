@extends('layouts.customer')

@section('content')
<div>
    <h1 class="text-2xl font-bold text-gray-800 mb-6">My Invoices</h1>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr class="text-left text-xs text-gray-500 uppercase tracking-wider">
                    <th class="px-4 py-3">Invoice</th>
                    <th class="px-4 py-3">Service</th>
                    <th class="px-4 py-3">Amount</th>
                    <th class="px-4 py-3">Due Date</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($invoices as $invoice)
                    @php
                        $colors = ['draft'=>'gray','unpaid'=>'yellow','paid'=>'green','cancelled'=>'red'];
                        $c = $colors[$invoice->status] ?? 'gray';
                    @endphp
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3">
                            <p class="font-mono text-xs font-semibold text-gray-700">{{ $invoice->invoice_number }}</p>
                            <p class="text-xs text-gray-400">{{ $invoice->created_at->format('d M Y') }}</p>
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ $invoice->booking->items->pluck('service.name')->join(', ') }}
                        </td>
                        <td class="px-4 py-3 font-semibold text-gray-800">₦{{ number_format($invoice->total, 2) }}</td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ $invoice->due_date->format('d M Y') }}
                            @if($invoice->isOverdue())
                                <span class="text-red-500 text-xs block">Overdue</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-{{ $c }}-100 text-{{ $c }}-700">
                                {{ ucfirst($invoice->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @if($invoice->status === 'unpaid')
                                <a href="{{ route('customer.checkout', $invoice->id) }}"
                                   class="text-xs bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-lg transition">
                                    Pay Now
                                </a>
                            @else
                                <a href="{{ route('customer.invoices.show', $invoice->id) }}"
                                   class="text-xs border border-gray-300 text-gray-600 hover:bg-gray-50 px-3 py-1.5 rounded-lg transition">
                                    View
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-12 text-gray-400">No invoices yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $invoices->links() }}</div>
</div>
@endsection

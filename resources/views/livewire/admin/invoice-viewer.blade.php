<div>
    <div class="flex items-center justify-between mb-6">
        <div class="flex gap-3">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search invoice / customer…"
                   class="border border-gray-300 rounded-lg px-4 py-2 text-sm w-64 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
            <select wire:model.live="statusFilter" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">All Statuses</option>
                <option value="draft">Draft</option>
                <option value="unpaid">Unpaid</option>
                <option value="paid">Paid</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr class="text-left text-xs text-gray-500 uppercase tracking-wider">
                    <th class="px-4 py-3">Invoice #</th>
                    <th class="px-4 py-3">Customer</th>
                    <th class="px-4 py-3">Booking</th>
                    <th class="px-4 py-3">Amount</th>
                    <th class="px-4 py-3">Due Date</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($invoices as $invoice)
                    @php
                        $colors = ['draft'=>'gray','unpaid'=>'yellow','paid'=>'green','cancelled'=>'red'];
                        $c = $colors[$invoice->status] ?? 'gray';
                    @endphp
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 font-mono text-xs font-semibold text-gray-700">{{ $invoice->invoice_number }}</td>
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $invoice->booking->customer->name }}</td>
                        <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $invoice->booking->booking_reference }}</td>
                        <td class="px-4 py-3 font-semibold text-gray-800">₦{{ number_format($invoice->total, 2) }}</td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ $invoice->due_date->format('d M Y') }}
                            @if($invoice->isOverdue())
                                <span class="text-red-500 text-xs"> (Overdue)</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded-full text-xs font-medium bg-{{ $c }}-100 text-{{ $c }}-700">
                                {{ ucfirst($invoice->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex gap-1">
                                <button wire:click="$set('viewingId', {{ $invoice->id }})"
                                        class="text-xs bg-blue-100 hover:bg-blue-200 text-blue-700 px-2 py-1 rounded">View</button>
                                @if($invoice->status === 'unpaid')
                                    <button wire:click="markPaid({{ $invoice->id }})"
                                            wire:confirm="Mark this invoice as paid (cash)?"
                                            class="text-xs bg-green-100 hover:bg-green-200 text-green-700 px-2 py-1 rounded">Mark Paid</button>
                                    <button wire:click="cancel({{ $invoice->id }})"
                                            wire:confirm="Cancel this invoice?"
                                            class="text-xs bg-red-100 hover:bg-red-200 text-red-600 px-2 py-1 rounded">Cancel</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-10 text-gray-400">No invoices found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $invoices->links() }}</div>

    {{-- Invoice Detail Modal --}}
    @if($viewingInvoice)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h2 class="text-lg font-bold text-gray-800">{{ $viewingInvoice->invoice_number }}</h2>
                    <p class="text-sm text-gray-400">Booking: {{ $viewingInvoice->booking->booking_reference }}</p>
                </div>
                <span class="px-3 py-1 rounded-full text-sm font-medium
                    {{ $viewingInvoice->status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                    {{ ucfirst($viewingInvoice->status) }}
                </span>
            </div>
            <div class="border-t border-b border-gray-100 py-4 mb-4 space-y-2 text-sm">
                <div class="flex justify-between text-gray-600">
                    <span>Customer</span>
                    <span class="font-medium">{{ $viewingInvoice->booking->customer->name }}</span>
                </div>
                <div class="flex justify-between text-gray-600">
                    <span>Services</span>
                    <span class="font-medium text-right">{{ $viewingInvoice->booking->items->pluck('service.name')->join(', ') }}</span>
                </div>
                <div class="flex justify-between text-gray-600">
                    <span>Subtotal</span>
                    <span>₦{{ number_format($viewingInvoice->amount, 2) }}</span>
                </div>

                @if(\App\Services\InvoiceService::vatEnabled() && $viewingInvoice->tax > 0)
                    <div class="flex justify-between text-gray-600">
                        <span>{{ \App\Services\InvoiceService::vatLabel() }}</span>
                        <span>₦{{ number_format($viewingInvoice->tax, 2) }}</span>
                    </div>
                @endif

                <div class="flex justify-between text-gray-600">
                    <span>Discount</span>
                    <span>- ₦{{ number_format($viewingInvoice->discount, 2) }}</span>
                </div>
                <div class="flex justify-between font-bold text-gray-800 text-base pt-2 border-t border-gray-100">
                    <span>Total</span>
                    <span>₦{{ number_format($viewingInvoice->total, 2) }}</span>
                </div>
            </div>
            @if($viewingInvoice->payment)
                <p class="text-xs text-green-600 mb-3">✓ Paid via {{ ucfirst($viewingInvoice->payment->payment_method) }}
                    on {{ $viewingInvoice->paid_at?->format('d M Y') }}</p>
            @endif
            <button wire:click="$set('viewingId', null)"
                    class="w-full border border-gray-300 text-gray-600 hover:bg-gray-50 py-2 rounded-lg text-sm">
                Close
            </button>
        </div>
    </div>
    @endif
</div>

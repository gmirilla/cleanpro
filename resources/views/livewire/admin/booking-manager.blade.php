<div>
    {{-- ── Page Header ─────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Bookings</h1>
            <p class="text-sm text-gray-500 mt-0.5">Manage all customer bookings</p>
        </div>
    </div>

    {{-- ── Filters ──────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-6 flex flex-wrap gap-3">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search by reference, customer…"
            class="flex-1 min-w-[200px] border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />

        <select wire:model.live="statusFilter"
            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="">All Statuses</option>
            <option value="pending">Pending</option>
            <option value="confirmed">Confirmed</option>
            <option value="assigned">Assigned</option>
            <option value="in_progress">In Progress</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
        </select>

        <input wire:model.live="dateFilter" type="date"
            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
    </div>

    {{-- ── Bookings Table ───────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider">
                            Reference</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider">
                            Customer</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider">
                            Services</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider">
                            Service Date</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider">
                            Amount</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider">
                            Staff</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider">
                            Status</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider">
                            Invoice</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($bookings as $booking)
                        @php
                            $colors = [
                                'pending' => 'yellow',
                                'confirmed' => 'blue',
                                'assigned' => 'indigo',
                                'in_progress' => 'purple',
                                'completed' => 'green',
                                'cancelled' => 'red',
                            ];
                            $c = $colors[$booking->status] ?? 'gray';
                        @endphp
                        <tr class="hover:bg-gray-50 transition {{ $viewingId === $booking->id ? 'bg-indigo-50' : '' }}">
                            <td class="px-4 py-3 font-mono text-xs font-semibold text-gray-700">
                                {{ $booking->booking_reference }}
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-800">{{ $booking->customer->name }}</p>
                                <p class="text-xs text-gray-400">{{ $booking->customer->email }}</p>
                            </td>
                            <td class="px-4 py-3 text-gray-600 max-w-[160px] truncate">
                                {{ $booking->items->pluck('service.name')->join(', ') }}
                            </td>
                            <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                                {{ $booking->service_date->format('d M Y') }}<br>
                                <span class="text-xs text-gray-400">{{ $booking->service_date->format('h:i A') }}</span>
                            </td>
                            <td class="px-4 py-3 font-semibold text-gray-800">
                                ₦{{ number_format($booking->total_amount, 2) }}
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ $booking->assignedStaff?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="px-2 py-1 rounded-full text-xs font-semibold bg-{{ $c }}-100 text-{{ $c }}-700 whitespace-nowrap">
                                    {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @if ($booking->invoice)
                                    <span
                                        class="text-xs {{ $booking->invoice->isPaid() ? 'text-green-600 font-semibold' : 'text-orange-500' }}">
                                        {{ $booking->invoice->isPaid() ? '✓ Paid' : '⚠ Unpaid' }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">No invoice</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <button wire:click="viewBooking({{ $booking->id }})"
                                    class="text-xs bg-gray-100 hover:bg-indigo-100 text-gray-700 hover:text-indigo-700 px-3 py-1.5 rounded-lg transition font-medium">
                                    View Details
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-16 text-gray-400">
                                <div class="text-4xl mb-2">📋</div>
                                <p>No bookings found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $bookings->links() }}</div>


    {{-- ════════════════════════════════════════════════════════════
         BOOKING DETAIL DRAWER
    ════════════════════════════════════════════════════════════ --}}
    @if ($viewingBooking)
        @php
            $b = $viewingBooking;
            $colors = [
                'pending' => 'yellow',
                'confirmed' => 'blue',
                'assigned' => 'indigo',
                'in_progress' => 'purple',
                'completed' => 'green',
                'cancelled' => 'red',
            ];
            $bc = $colors[$b->status] ?? 'gray';
        @endphp
        {{-- Backdrop --}}
        <div wire:click="closeDrawer" class="fixed inset-0 z-40 bg-black/30 backdrop-blur-sm transition-opacity"></div>

        {{-- Drawer panel --}}
        <div class="fixed inset-y-0 right-0 z-50 w-full max-w-2xl bg-white shadow-2xl flex flex-col overflow-hidden">

            {{-- Drawer header --}}
            <div class="flex items-start justify-between px-6 py-5 border-b border-gray-200 bg-gray-50">
                <div>
                    <div class="flex items-center gap-3">
                        <span class="font-mono text-sm font-bold text-gray-500">{{ $b->booking_reference }}</span>
                        <span
                            class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-{{ $bc }}-100 text-{{ $bc }}-700">
                            {{ ucfirst(str_replace('_', ' ', $b->status)) }}
                        </span>
                        @if ($b->invoice)
                            <span
                                class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $b->invoice->isPaid() ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700' }}">
                                Invoice: {{ ucfirst($b->invoice->status) }}
                            </span>
                        @endif
                    </div>
                    <p class="text-lg font-bold text-gray-900 mt-1">{{ $b->customer->name }}</p>
                    <p class="text-sm text-gray-400">{{ $b->customer->email }}</p>
                </div>
                <button wire:click="closeDrawer"
                    class="text-gray-400 hover:text-gray-700 text-2xl leading-none mt-1">&times;</button>
            </div>

            {{-- Drawer body (scrollable) --}}
            <div class="flex-1 overflow-y-auto px-6 py-5 space-y-6">

                {{-- ── Admin Action Buttons ── --}}
                @if ($b->status !== 'cancelled')
                    <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4">
                        <p class="text-xs font-semibold text-indigo-700 uppercase tracking-wider mb-3">Admin Actions</p>
                        <div class="flex flex-wrap gap-2">

                            {{-- Confirm --}}
                            @if ($b->status === 'pending')
                                <button wire:click="openConfirm({{ $b->id }})"
                                    class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition shadow-sm">
                                    ✅ Confirm Booking
                                </button>
                            @endif

                            {{-- Reject --}}
                            @if (in_array($b->status, ['pending', 'confirmed']))
                                <button wire:click="openReject({{ $b->id }})"
                                    class="inline-flex items-center gap-1.5 bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition shadow-sm">
                                    ✖ Reject Booking
                                </button>
                            @endif

                            {{-- Assign Staff --}}
                            @if (in_array($b->status, ['pending', 'confirmed', 'assigned']))
                                <button wire:click="openAssign({{ $b->id }})"
                                    class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition shadow-sm">
                                    👷 {{ $b->assignedStaff ? 'Reassign Staff' : 'Assign Staff' }}
                                </button>
                            @endif

                            {{-- Mark In Progress --}}
                            @if ($b->status === 'assigned')
                                <button wire:click="updateStatus({{ $b->id }}, 'in_progress')"
                                    class="inline-flex items-center gap-1.5 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition shadow-sm">
                                    🔄 Start Job
                                </button>
                            @endif

                            {{-- Mark Completed --}}
                            @if ($b->status === 'in_progress')
                                <button wire:click="updateStatus({{ $b->id }}, 'completed')"
                                    class="inline-flex items-center gap-1.5 bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition shadow-sm">
                                    🏁 Mark Completed
                                </button>
                            @endif

                            {{-- Generate Invoice --}}
                            @if (!$b->invoice && in_array($b->status, ['confirmed', 'assigned', 'in_progress', 'completed']))
                                <button wire:click="openGenerateInvoice({{ $b->id }})"
                                    class="inline-flex items-center gap-1.5 bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition shadow-sm">
                                    🧾 Generate Invoice
                                </button>
                            @elseif($b->invoice)
                                <span
                                    class="inline-flex items-center gap-1.5 bg-gray-100 text-gray-500 text-sm font-medium px-4 py-2 rounded-lg">
                                    🧾 Invoice #{{ $b->invoice->invoice_number }}
                                </span>
                            @endif

                        </div>
                    </div>
                @else
                    <div class="bg-red-50 border border-red-100 rounded-xl p-4 text-sm text-red-700">
                        <strong>Booking Rejected/Cancelled</strong>
                        @if ($b->cancellation_reason)
                            <p class="mt-1 text-red-500">Reason: {{ $b->cancellation_reason }}</p>
                        @endif
                    </div>
                @endif

                {{-- ── Services / Items ── --}}
                <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                    <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                        <p class="text-sm font-semibold text-gray-700">Service Items</p>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @foreach ($b->items as $item)
                            <div class="flex justify-between items-center px-4 py-3">
                                <div>
                                    <p class="font-medium text-gray-800 text-sm">{{ $item->service->name }}</p>
                                    <p class="text-xs text-gray-400">{{ ucfirst($item->service->category ?? '') }} ·
                                        Qty: {{ $item->quantity }} × ₦{{ number_format($item->price, 2) }}</p>
                                </div>
                                <span
                                    class="font-semibold text-gray-700 text-sm">₦{{ number_format($item->subtotal, 2) }}</span>
                            </div>
                        @endforeach
                        <div class="flex justify-between items-center px-4 py-3 bg-gray-50 font-bold text-gray-900">
                            <span>Total</span>
                            <span>₦{{ number_format($b->total_amount, 2) }}</span>
                        </div>
                    </div>
                </div>

                {{-- ── Booking Info Grid ── --}}
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                        <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Service Date</p>
                        <p class="font-semibold text-gray-800 text-sm">{{ $b->service_date->format('D, d M Y') }}</p>
                        <p class="text-xs text-gray-500">{{ $b->service_date->format('h:i A') }}</p>
                    </div>
                    @if ($b->pickup_date)
                        <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Pickup Date</p>
                            <p class="font-semibold text-gray-800 text-sm">{{ $b->pickup_date->format('D, d M Y') }}
                            </p>
                            <p class="text-xs text-gray-500">{{ $b->pickup_date->format('h:i A') }}</p>
                        </div>
                    @endif
                    <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                        <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Assigned Staff</p>
                        @if ($b->assignedStaff)
                            <p class="font-semibold text-gray-800 text-sm">{{ $b->assignedStaff->name }}</p>
                            <p class="text-xs text-gray-500">{{ $b->assignedStaff->user->email ?? '' }}</p>
                        @else
                            <p class="text-sm text-gray-400 italic">Not yet assigned</p>
                        @endif
                    </div>
                    @if ($b->address)
                        <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Address</p>
                            <p class="font-semibold text-gray-800 text-sm">{{ $b->address->street }}</p>
                            <p class="text-xs text-gray-500">{{ $b->address->city }}, {{ $b->address->state }}</p>
                        </div>
                    @endif
                    @if ($b->confirmed_at)
                        <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Confirmed At</p>
                            <p class="font-semibold text-gray-800 text-sm">{{ $b->confirmed_at->format('d M Y') }}</p>
                            <p class="text-xs text-gray-500">{{ $b->confirmed_at->format('h:i A') }}</p>
                        </div>
                    @endif
                    @if ($b->completed_at)
                        <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Completed At</p>
                            <p class="font-semibold text-gray-800 text-sm">{{ $b->completed_at->format('d M Y') }}</p>
                            <p class="text-xs text-gray-500">{{ $b->completed_at->format('h:i A') }}</p>
                        </div>
                    @endif
                </div>

                {{-- ── Notes ── --}}
                @if ($b->notes)
                    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                        <p class="text-xs font-semibold text-yellow-700 uppercase tracking-wider mb-1">Notes</p>
                        <p class="text-sm text-gray-700">{{ $b->notes }}</p>
                    </div>
                @endif

                {{-- ── Invoice Summary ── --}}
                @if ($b->invoice)
                    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                        <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                            <p class="text-sm font-semibold text-gray-700">Invoice</p>
                            <span class="font-mono text-xs text-gray-500">{{ $b->invoice->invoice_number }}</span>
                        </div>
                        <div class="px-4 py-3 space-y-2 text-sm">
                            <div class="flex justify-between text-gray-600">
                                <span>Subtotal</span>
                                <span>₦{{ number_format($b->invoice->amount, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>VAT (7.5%)</span>
                                <span>₦{{ number_format($b->invoice->tax, 2) }}</span>
                            </div>
                            @if ($b->invoice->discount > 0)
                                <div class="flex justify-between text-green-600">
                                    <span>Discount</span>
                                    <span>-₦{{ number_format($b->invoice->discount, 2) }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between font-bold text-gray-900 pt-2 border-t border-gray-100">
                                <span>Total</span>
                                <span>₦{{ number_format($b->invoice->total, 2) }}</span>
                            </div>
                            <div class="flex justify-between items-center pt-1">
                                <span class="text-gray-400 text-xs">Due:
                                    {{ \Carbon\Carbon::parse($b->invoice->due_date)->format('d M Y') }}</span>
                                <span
                                    class="px-2.5 py-0.5 rounded-full text-xs font-semibold
                            {{ $b->invoice->status === 'paid'
                                ? 'bg-green-100 text-green-700'
                                : ($b->invoice->status === 'cancelled'
                                    ? 'bg-red-100 text-red-700'
                                    : 'bg-orange-100 text-orange-700') }}">
                                    {{ ucfirst($b->invoice->status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endif

            </div>{{-- end scrollable body --}}
        </div>
    @endif


    {{-- ════════════════════════════════════════════════════════════
         CONFIRM BOOKING MODAL
    ════════════════════════════════════════════════════════════ --}}
    @if ($confirmingId)
        <div class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 px-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6">
                <div class="text-center mb-4">
                    <div class="text-4xl mb-2">✅</div>
                    <h2 class="text-lg font-bold text-gray-900">Confirm Booking?</h2>
                    <p class="text-sm text-gray-500 mt-1">
                        This will confirm the booking and automatically generate an invoice for the customer.
                    </p>
                </div>
                <div class="flex gap-3">
                    <button wire:click="$set('confirmingId', null)"
                        class="flex-1 border border-gray-300 text-gray-700 py-2 rounded-lg text-sm font-medium hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button wire:click="confirmBooking"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg text-sm font-medium transition">
                        Yes, Confirm
                    </button>
                </div>
            </div>
        </div>
    @endif


    {{-- ════════════════════════════════════════════════════════════
         REJECT BOOKING MODAL
    ════════════════════════════════════════════════════════════ --}}
    @if ($rejectingId)
        <div class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 px-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6">
                <div class="text-center mb-4">
                    <div class="text-4xl mb-2">✖</div>
                    <h2 class="text-lg font-bold text-gray-900">Reject Booking?</h2>
                    <p class="text-sm text-gray-500 mt-1">Optionally provide a reason. The customer will be notified.
                    </p>
                </div>
                <textarea wire:model="cancellationReason" placeholder="Reason for rejection (optional)…" rows="3"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400 mb-4 resize-none"></textarea>
                <div class="flex gap-3">
                    <button wire:click="$set('rejectingId', null)"
                        class="flex-1 border border-gray-300 text-gray-700 py-2 rounded-lg text-sm font-medium hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button wire:click="confirmReject"
                        class="flex-1 bg-red-600 hover:bg-red-700 text-white py-2 rounded-lg text-sm font-medium transition">
                        Yes, Reject
                    </button>
                </div>
            </div>
        </div>
    @endif


    {{-- ════════════════════════════════════════════════════════════
         ASSIGN STAFF MODAL
    ════════════════════════════════════════════════════════════ --}}
    @if ($assigningId)
        <div class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 px-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-gray-900">Assign Staff</h2>
                    <button wire:click="$set('assigningId', null)"
                        class="text-gray-400 hover:text-gray-700 text-xl">&times;</button>
                </div>
                <select wire:model="selectedStaff"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 mb-4">
                    <option value="">Select available staff…</option>
                    @foreach ($availableStaff as $s)
                        <option value="{{ $s->id }}">{{ $s->user->name }} —
                            {{ ucfirst($s->role ?? 'Staff') }}</option>
                    @endforeach
                </select>
                @error('selectedStaff')
                    <p class="text-red-500 text-xs mb-3">{{ $message }}</p>
                @enderror
                <div class="flex gap-3">
                    <button wire:click="$set('assigningId', null)"
                        class="flex-1 border border-gray-300 text-gray-700 py-2 rounded-lg text-sm font-medium hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button wire:click="confirmAssign"
                        class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-lg text-sm font-medium transition">
                        Assign
                    </button>
                </div>
            </div>
        </div>
    @endif


    {{-- ════════════════════════════════════════════════════════════
         GENERATE INVOICE MODAL
    ════════════════════════════════════════════════════════════ --}}
    @if ($generatingInvoiceId)
        <div class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 px-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6">
                <div class="text-center mb-4">
                    <div class="text-4xl mb-2">🧾</div>
                    <h2 class="text-lg font-bold text-gray-900">Generate Invoice?</h2>
                    <p class="text-sm text-gray-500 mt-1">
                        An invoice will be created and the customer will be notified via email.
                    </p>
                </div>
                <div class="flex gap-3">
                    <button wire:click="$set('generatingInvoiceId', null)"
                        class="flex-1 border border-gray-300 text-gray-700 py-2 rounded-lg text-sm font-medium hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button wire:click="generateInvoice"
                        class="flex-1 bg-amber-500 hover:bg-amber-600 text-white py-2 rounded-lg text-sm font-medium transition">
                        Generate Invoice
                    </button>
                </div>
            </div>
        </div>
    @endif



</div>

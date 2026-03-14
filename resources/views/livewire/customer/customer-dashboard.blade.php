<div>
    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        @foreach([
            ['label'=>'Total Bookings', 'val'=>$stats['total_bookings'], 'color'=>'indigo'],
            ['label'=>'Completed',      'val'=>$stats['completed'],      'color'=>'green'],
            ['label'=>'Upcoming',       'val'=>$stats['upcoming'],       'color'=>'blue'],
            ['label'=>'Unpaid Invoices','val'=>$stats['unpaid_invoices'],'color'=>'red'],
        ] as $s)
            <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100 text-center">
                <p class="text-xs text-gray-500 uppercase tracking-wider">{{ $s['label'] }}</p>
                <p class="text-3xl font-bold text-{{ $s['color'] }}-600 mt-2">{{ $s['val'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Recent Bookings --}}
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-semibold text-gray-700">Recent Bookings</h3>
                <a href="{{ route('customer.bookings') }}" class="text-sm text-indigo-600 hover:underline">View all →</a>
            </div>
            <div class="space-y-3">
                @forelse($recentBookings as $booking)
                    @php
                        $colors = ['pending'=>'yellow','confirmed'=>'blue','assigned'=>'indigo',
                                   'in_progress'=>'purple','completed'=>'green','cancelled'=>'red'];
                        $c = $colors[$booking->status] ?? 'gray';
                    @endphp
                    <div class="flex items-center justify-between py-3 border-b border-gray-50">
                        <div>
                            <p class="font-medium text-gray-800 text-sm">{{ $booking->items->pluck('service.name')->join(', ') }}</p>
                            <p class="text-xs text-gray-400">{{ $booking->booking_reference }} · {{ $booking->service_date->format('d M Y') }}</p>
                        </div>
                        <div class="text-right">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $c }}-100 text-{{ $c }}-700">
                                {{ ucfirst(str_replace('_',' ',$booking->status)) }}
                            </span>
                            <p class="text-xs font-semibold text-gray-700 mt-1">₦{{ number_format($booking->total_amount, 2) }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-400 text-sm text-center py-4">No bookings yet.</p>
                @endforelse
            </div>
        </div>

        {{-- Pending Invoices + Quick Actions --}}
        <div class="space-y-4">
            @if($pendingInvoices->count())
                <div class="bg-red-50 border border-red-100 rounded-xl p-5">
                    <h3 class="font-semibold text-red-700 mb-3">⚠ Unpaid Invoices</h3>
                    @foreach($pendingInvoices as $invoice)
                        <div class="flex justify-between items-center py-2 border-b border-red-100 last:border-0">
                            <div>
                                <p class="text-sm font-medium text-red-800">{{ $invoice->invoice_number }}</p>
                                <p class="text-xs text-red-500">Due {{ $invoice->due_date->format('d M Y') }}</p>
                            </div>
                            <a href="{{ route('customer.checkout', $invoice->id) }}"
                               class="bg-red-600 hover:bg-red-700 text-white text-xs px-3 py-1.5 rounded-lg transition">
                                Pay ₦{{ number_format($invoice->total, 2) }}
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="bg-white rounded-xl shadow-sm p-5">
                <h3 class="font-semibold text-gray-700 mb-3">Quick Actions</h3>
                <div class="space-y-2">
                    <a href="{{ route('customer.book') }}"
                       class="flex items-center gap-2 w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-3 rounded-lg text-sm font-medium transition">
                        🧹 Book a Service
                    </a>
                    <a href="{{ route('customer.bookings') }}"
                       class="flex items-center gap-2 w-full border border-gray-300 hover:bg-gray-50 text-gray-700 px-4 py-3 rounded-lg text-sm transition">
                        📋 View All Bookings
                    </a>
                    <a href="{{ route('customer.invoices') }}"
                       class="flex items-center gap-2 w-full border border-gray-300 hover:bg-gray-50 text-gray-700 px-4 py-3 rounded-lg text-sm transition">
                        🧾 My Invoices
                    </a>
                </div>
            </div>

            {{-- Notifications --}}
            @if($notifications->count())
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <h3 class="font-semibold text-gray-700 mb-3">Notifications</h3>
                    <div class="space-y-2">
                        @foreach($notifications as $notif)
                            <div class="flex items-start gap-2 text-xs text-gray-600 py-1 border-b border-gray-50 last:border-0">
                                <span class="text-indigo-400 mt-0.5">●</span>
                                {{ $notif->data['message'] ?? 'New notification' }}
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

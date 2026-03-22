@extends('layouts.customer')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('customer.bookings') }}" class="text-gray-400 hover:text-gray-600 text-sm">← All Bookings</a>
        <h1 class="text-2xl font-bold text-gray-800">{{ $booking->booking_reference }}</h1>
        @php
            $colors = ['pending'=>'yellow','confirmed'=>'blue','assigned'=>'indigo',
                       'in_progress'=>'purple','completed'=>'green','cancelled'=>'red'];
            $c = $colors[$booking->status] ?? 'gray';
        @endphp
        <span class="px-3 py-1 rounded-full text-sm font-semibold bg-{{ $c }}-100 text-{{ $c }}-700">
            {{ ucfirst(str_replace('_',' ',$booking->status)) }}
        </span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Main details --}}
        <div class="md:col-span-2 space-y-4">
            <div class="bg-white rounded-xl border border-gray-100 p-5">
                <h3 class="font-semibold text-gray-700 mb-4">Service Details</h3>
                <div class="space-y-3">
                    @foreach($booking->items as $item)
                        <div class="flex justify-between items-center py-2 border-b border-gray-50 last:border-0">
                            <div>
                                <p class="font-medium text-gray-800">{{ $item->service->name }}</p>
                                <p class="text-xs text-gray-400">{{ ucfirst($item->service->category) }} · Qty: {{ $item->quantity }}</p>
                            </div>
                            <span class="font-semibold text-gray-700">₦{{ number_format($item->subtotal, 2) }}</span>
                        </div>
                    @endforeach
                               @if($booking->laundryOrder)
            <div class="bg-white rounded-xl border border-gray-100 p-5">
                <h3 class="font-semibold text-gray-700 mb-4">Laundry Order</h3>
                <div class="grid grid-cols-2 gap-3 text-sm mb-4">
                    @if($booking->laundryOrder->weight)
                        <div><p class="text-gray-400 text-xs">Weight</p><p class="font-medium">{{ $booking->laundryOrder->weight }}kg</p></div>
                    @endif
                    <div><p class="text-gray-400 text-xs">Detergent</p><p class="font-medium">{{ ucfirst(str_replace('_',' ',$booking->laundryOrder->detergent_type)) }}</p></div>
                    @if($booking->laundryOrder->express_service)
                        <div><p class="text-xs bg-orange-100 text-orange-700 px-2 py-0.5 rounded-full inline-block">⚡ Express Service</p></div>
                    @endif
                </div>
                @if($booking->laundryOrder->items->count())
                <table class="w-full text-sm">
                    <thead><tr class="text-xs text-gray-400 border-b"><th class="text-left pb-1">Garment</th>
                        <th class="text-center pb-1">Qty</th><th>Cost </th><th class="text-left pb-1">Status</th></tr></thead>
                    <tbody>
                        @foreach($booking->laundryOrder->items as $item)
                        <tr class="border-b border-gray-50">
                            <td class="py-1.5">{{ ucfirst($item->garment_type) }}</td>
                            <td class="text-center py-1.5">{{ $item->quantity }}</td>
                            <td class="text-center py-1.5">₦{{ number_format($item->subtotal, 2) }}</td>
                            <td class="py-1.5">
                                <span class="px-2 py-0.5 rounded-full text-xs bg-{{ $item->status_badge_color }}-100 text-{{ $item->status_badge_color }}-700">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
            @endif
                    <div class="flex justify-between font-bold text-gray-800 pt-2">
                        <span>Total</span>
                        <span>₦{{ number_format($booking->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-100 p-5">
                <h3 class="font-semibold text-gray-700 mb-4">Booking Info</h3>
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div><p class="text-gray-400 text-xs">Service Date</p>
                         <p class="font-medium">{{ $booking->service_date->format('D, d M Y h:i A') }}</p></div>
                    @if($booking->pickup_date)
                    <div><p class="text-gray-400 text-xs">Pickup Date</p>
                         <p class="font-medium">{{ $booking->pickup_date->format('D, d M Y h:i A') }}</p></div>
                    @endif
                    @if($booking->address)
                    <div class="col-span-2"><p class="text-gray-400 text-xs">Address</p>
                         <p class="font-medium">{{ $booking->address->full_address }}</p></div>
                    @endif
                    @if($booking->assignedStaff)
                    <div><p class="text-gray-400 text-xs">Assigned Staff</p>
                         <p class="font-medium">{{ $booking->assignedStaff->name }}</p>
                         <p class="text-xs text-yellow-500">★ {{ number_format($booking->assignedStaff->rating, 1) }}</p></div>
                    @endif
                    @if($booking->notes)
                    <div class="col-span-2"><p class="text-gray-400 text-xs">Notes</p>
                         <p class="text-gray-600">{{ $booking->notes }}</p></div>
                    @endif
                </div>
            </div>


            @if($booking->photos->count())
            <div class="bg-white rounded-xl border border-gray-100 p-5">
                <h3 class="font-semibold text-gray-700 mb-3">Job Photos</h3>
                <div class="grid grid-cols-3 gap-2">
                    @foreach($booking->photos as $photo)
                        <a href="{{ $photo->url }}" target="_blank">
                            <img src="{{ $photo->url }}" alt="{{ $photo->caption }}"
                                 class="w-full h-24 object-cover rounded-lg hover:opacity-90 transition border border-gray-100">
                        </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- Sidebar: Invoice & Actions --}}
        <div class="space-y-4">
            @if($booking->invoice)
            <div class="bg-white rounded-xl border border-gray-100 p-5">
                <h3 class="font-semibold text-gray-700 mb-3">Invoice</h3>
                <p class="font-mono text-sm text-gray-600 mb-2">{{ $booking->invoice->invoice_number }}</p>
                <div class="space-y-1 text-sm mb-4">
                    <div class="flex justify-between text-gray-500"><span>Amount</span><span>₦{{ number_format($booking->invoice->amount,2) }}</span></div>
                    <div class="flex justify-between text-gray-500"><span>Tax</span><span>₦{{ number_format($booking->invoice->tax,2) }}</span></div>
                    <div class="flex justify-between font-bold text-gray-800 border-t border-gray-100 pt-1 mt-1"><span>Total</span><span>₦{{ number_format($booking->invoice->total,2) }}</span></div>
                </div>
                @if($booking->invoice->isPaid())
                    <div class="text-center py-2 bg-green-50 rounded-lg">
                        <p class="text-green-700 font-semibold text-sm">✓ Paid</p>
                        <p class="text-xs text-green-500">{{ $booking->invoice->paid_at?->format('d M Y') }}</p>
                    </div>
                @else
                    <a href="{{ route('customer.checkout', $booking->invoice->id) }}"
                       class="block text-center w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2.5 rounded-lg text-sm font-medium transition">
                        Pay Now
                    </a>
                @endif
            </div>
            @endif

            @if($booking->status === 'completed' && !$booking->reviews->count())
            <div class="bg-yellow-50 border border-yellow-100 rounded-xl p-5">
                <p class="text-sm font-semibold text-yellow-800 mb-1">Rate this Service</p>
                <p class="text-xs text-yellow-600">Share your experience with us!</p>
                {{-- Review form could be a Livewire component --}}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

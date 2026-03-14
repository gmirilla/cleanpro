@extends('layouts.customer')

@section('content')
<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">My Bookings</h1>
        <a href="{{ route('customer.book') }}"
           class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-lg text-sm font-medium transition">
            + New Booking
        </a>
    </div>

    <div class="space-y-4">
        @forelse($bookings as $booking)
            @php
                $colors = ['pending'=>'yellow','confirmed'=>'blue','assigned'=>'indigo',
                           'in_progress'=>'purple','completed'=>'green','cancelled'=>'red'];
                $c = $colors[$booking->status] ?? 'gray';
            @endphp
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:border-indigo-200 transition">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="font-mono text-xs font-bold text-gray-400">{{ $booking->booking_reference }}</span>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-{{ $c }}-100 text-{{ $c }}-700">
                                {{ ucfirst(str_replace('_',' ',$booking->status)) }}
                            </span>
                        </div>
                        <p class="font-semibold text-gray-800">
                            {{ $booking->items->pluck('service.name')->join(', ') }}
                        </p>
                        <div class="flex flex-wrap gap-4 mt-2 text-sm text-gray-500">
                            <span>📅 {{ $booking->service_date->format('D, d M Y h:i A') }}</span>
                            @if($booking->assignedStaff)
                                <span>👷 {{ $booking->assignedStaff->name }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="text-right ml-4">
                        <p class="text-lg font-bold text-gray-800">₦{{ number_format($booking->total_amount,2) }}</p>
                        @if($booking->invoice)
                            <span class="text-xs {{ $booking->invoice->isPaid() ? 'text-green-600' : 'text-red-500' }}">
                                {{ $booking->invoice->isPaid() ? '✓ Paid' : '⚠ Unpaid' }}
                            </span>
                        @endif
                    </div>
                </div>
                <div class="flex gap-2 mt-4">
                    <a href="{{ route('customer.bookings.show', $booking->id) }}"
                       class="text-xs border border-gray-300 hover:border-indigo-400 text-gray-600 hover:text-indigo-600 px-3 py-1.5 rounded-lg transition">
                        View Details
                    </a>
                    @if($booking->invoice && !$booking->invoice->isPaid())
                        <a href="{{ route('customer.checkout', $booking->invoice->id) }}"
                           class="text-xs bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-lg transition">
                            Pay Invoice
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center py-16 bg-white rounded-xl border border-gray-100">
                <p class="text-5xl mb-3">📋</p>
                <p class="text-gray-500 mb-4">No bookings yet.</p>
                <a href="{{ route('customer.book') }}"
                   class="bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
                    Book Your First Service
                </a>
            </div>
        @endforelse
    </div>

    <div class="mt-6">{{ $bookings->links() }}</div>
</div>
@endsection

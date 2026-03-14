<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $customer = $request->user()->customer;
        abort_unless($customer, 404);

        $bookings = Booking::where('customer_id', $customer->id)
            ->with(['items.service', 'assignedStaff.user', 'invoice'])
            ->latest()
            ->paginate(10);

        return view('customer.bookings.index', compact('bookings'));
    }

    public function show(int $id, Request $request)
    {
        $customer = $request->user()->customer;
        abort_unless($customer, 404);

        $booking = Booking::where('customer_id', $customer->id)
            ->with(['items.service', 'assignedStaff.user', 'invoice.payment', 'photos', 'laundryOrder.items', 'address'])
            ->findOrFail($id);

        return view('customer.bookings.show', compact('booking'));
    }
}

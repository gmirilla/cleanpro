<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\Staff;
use App\Repositories\BookingRepository;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BookingController extends Controller
{
    public function __construct(
        private BookingRepository $bookingRepo,
        private BookingService    $bookingService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['search', 'status', 'date', 'staff_id']);

        // Customers only see their own bookings
        if ($request->user()->isCustomer()) {
            $filters['customer_id'] = $request->user()->customer?->id;
        }

        return BookingResource::collection($this->bookingRepo->paginate(15, $filters));
    }

    public function show(Booking $booking): BookingResource
    {
        $this->authorize('view', $booking);
        return new BookingResource($booking->load(['customer.user', 'items.service', 'assignedStaff.user', 'invoice', 'photos']));
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Booking::class);

        $data = $request->validate([
            'address_id'   => 'nullable|exists:addresses,id',
            'service_date' => 'required|date|after:now',
            'pickup_date'  => 'nullable|date|after:now',
            'notes'        => 'nullable|string|max:500',
            'items'        => 'required|array|min:1',
            'items.*.service_id' => 'required|exists:services,id',
            'items.*.quantity'   => 'required|integer|min:1',
            'items.*.price'      => 'required|numeric|min:0',
        ]);

        $data['customer_id'] = $request->user()->customer->id;
        $booking = $this->bookingService->create($data);

        return response()->json(new BookingResource($booking), 201);
    }

    public function update(Request $request, Booking $booking): BookingResource
    {
        $this->authorize('update', $booking);

        $data = $request->validate([
            'service_date' => 'sometimes|date|after:now',
            'notes'        => 'nullable|string|max:500',
        ]);

        $booking->update($data);
        return new BookingResource($booking->fresh());
    }

    public function destroy(Booking $booking): JsonResponse
    {
        $this->authorize('delete', $booking);
        $booking->delete();
        return response()->json(['message' => 'Booking deleted.']);
    }

    public function updateStatus(Request $request, Booking $booking): BookingResource
    {
        $this->authorize('updateStatus', $booking);

        $data = $request->validate([
            'status' => 'required|in:confirmed,assigned,in_progress,completed,cancelled',
        ]);

        $this->bookingService->updateStatus($booking, $data['status']);
        return new BookingResource($booking->fresh());
    }

    public function assignStaff(Request $request, Booking $booking): BookingResource
    {
        $this->authorize('assignStaff', Booking::class);

        $data  = $request->validate(['staff_id' => 'required|exists:staff,id']);
        $staff = Staff::findOrFail($data['staff_id']);
        $this->bookingService->assignStaff($booking, $staff);

        return new BookingResource($booking->fresh(['assignedStaff.user']));
    }

    public function cancel(Request $request, Booking $booking): BookingResource
    {
        $this->authorize('cancel', $booking);

        $data = $request->validate(['reason' => 'nullable|string|max:500']);
        $this->bookingService->cancel($booking, $data['reason'] ?? '');

        return new BookingResource($booking->fresh());
    }
}

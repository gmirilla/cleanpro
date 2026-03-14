<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookingResource;
use App\Http\Resources\StaffResource;
use App\Models\Booking;
use App\Models\Staff;
use App\Repositories\StaffRepository;
use App\Services\StaffService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function __construct(
        private StaffRepository $repo,
        private StaffService    $service,
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Staff::class);
        return StaffResource::collection($this->repo->paginate(15, [
            'search'       => $request->search,
            'availability' => $request->availability,
        ]));
    }

    public function show(Staff $staff): StaffResource
    {
        $this->authorize('view', $staff);
        return new StaffResource($staff->load('user'));
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Staff::class);
        $data  = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users',
            'password'  => 'required|min:8',
            'phone'     => 'nullable|string',
            'position'  => 'required|string',
        ]);
        return response()->json(new StaffResource($this->service->create($data)), 201);
    }

    public function update(Request $request, Staff $staff): StaffResource
    {
        $this->authorize('update', $staff);
        $this->service->update($staff, $request->only('phone', 'position', 'availability_status', 'working_days', 'shift_start', 'shift_end', 'name'));
        return new StaffResource($staff->fresh(['user']));
    }

    public function destroy(Staff $staff): JsonResponse
    {
        $this->authorize('delete', $staff);
        $this->service->delete($staff);
        return response()->json(['message' => 'Staff deleted.']);
    }

    public function schedule(Request $request, Staff $staff)
    {
        $this->authorize('view', $staff);
        $bookings = Booking::where('assigned_staff_id', $staff->id)
            ->whereIn('status', ['assigned', 'in_progress', 'confirmed'])
            ->where('service_date', '>=', now())
            ->with('customer.user', 'items.service')
            ->orderBy('service_date')
            ->get();
        return BookingResource::collection($bookings);
    }
}

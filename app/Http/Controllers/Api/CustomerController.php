<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookingResource;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\InvoiceResource;
use App\Models\Booking;
use App\Models\Customer;
use App\Repositories\CustomerRepository;
use App\Services\CustomerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function __construct(
        private CustomerRepository $repo,
        private CustomerService    $service,
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Customer::class);
        return CustomerResource::collection($this->repo->paginate(15, ['search' => $request->search]));
    }

    public function show(Customer $customer): CustomerResource
    {
        $this->authorize('view', $customer);
        return new CustomerResource($customer->load('user', 'addresses'));
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Customer::class);
        $customer = $this->service->create($request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:8',
            'phone'    => 'nullable|string',
            'address'  => 'nullable|string',
            'city'     => 'nullable|string',
            'state'    => 'nullable|string',
        ]));
        return response()->json(new CustomerResource($customer), 201);
    }

    public function update(Request $request, Customer $customer): CustomerResource
    {
        $this->authorize('update', $customer);
        $customer->update($request->only('phone', 'notes'));
        return new CustomerResource($customer->fresh());
    }

    public function destroy(Customer $customer): JsonResponse
    {
        $this->authorize('delete', $customer);
        $customer->delete();
        return response()->json(['message' => 'Customer deleted.']);
    }

    public function orders(Request $request)
    {
        $customer = $request->user()->customer;
        abort_unless($customer, 404, 'No customer profile.');
        return BookingResource::collection(
            Booking::where('customer_id', $customer->id)->with('items.service')->latest()->paginate(10)
        );
    }

    public function invoices(Request $request)
    {
        $customer = $request->user()->customer;
        abort_unless($customer, 404);
        return InvoiceResource::collection(
            \App\Models\Invoice::whereHas('booking', fn($q) => $q->where('customer_id', $customer->id))
                ->latest()->paginate(10)
        );
    }

    public function profile(Request $request): CustomerResource
    {
        $customer = $request->user()->customer;
        abort_unless($customer, 404);
        return new CustomerResource($customer->load('user', 'addresses'));
    }

    public function updateProfile(Request $request): CustomerResource
    {
        $customer = $request->user()->customer;
        abort_unless($customer, 404);
        $data = $request->validate(['phone' => 'nullable|string', 'notes' => 'nullable|string']);
        $customer->update($data);
        if ($request->name) $customer->user->update(['name' => $request->name]);
        return new CustomerResource($customer->fresh());
    }
}

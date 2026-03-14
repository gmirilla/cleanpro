<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ServiceController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $services = Service::query()
            ->when($request->category, fn($q) => $q->where('category', $request->category))
            ->active()
            ->orderBy('category')
            ->orderBy('sort_order')
            ->get();

        return ServiceResource::collection($services);
    }

    public function show(Service $service): ServiceResource
    {
        return new ServiceResource($service);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Service::class);

        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'category'         => 'required|in:cleaning,laundry',
            'description'      => 'nullable|string',
            'base_price'       => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:15',
        ]);

        return response()->json(new ServiceResource(Service::create($data)), 201);
    }

    public function update(Request $request, Service $service): ServiceResource
    {
        $this->authorize('update', $service);

        $data = $request->validate([
            'name'             => 'sometimes|string|max:255',
            'category'         => 'sometimes|in:cleaning,laundry',
            'description'      => 'nullable|string',
            'base_price'       => 'sometimes|numeric|min:0',
            'duration_minutes' => 'sometimes|integer|min:15',
            'status'           => 'sometimes|in:active,inactive',
        ]);

        $service->update($data);
        return new ServiceResource($service);
    }

    public function destroy(Service $service): JsonResponse
    {
        $this->authorize('delete', $service);
        $service->delete();
        return response()->json(['message' => 'Service deleted.']);
    }
}

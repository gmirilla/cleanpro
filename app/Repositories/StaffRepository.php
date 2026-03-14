<?php

namespace App\Repositories;

use App\Models\Staff;
use Illuminate\Pagination\LengthAwarePaginator;

class StaffRepository extends BaseRepository
{
    public function __construct(Staff $model)
    {
        parent::__construct($model);
    }

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return Staff::query()
            ->when($filters['search'] ?? null, fn($q, $s) =>
                $q->whereHas('user', fn($u) => $u->where('name', 'like', "%{$s}%")))
            ->when($filters['availability'] ?? null, fn($q, $a) =>
                $q->where('availability_status', $a))
            ->with('user')
            ->withCount(['bookings as completed_count' => fn($q) => $q->where('status', 'completed')])
            ->paginate($perPage);
    }

    public function findAvailable(): ?Staff
    {
        return Staff::available()
            ->withCount(['bookings as active_count' => fn($q) =>
                $q->whereIn('status', ['assigned', 'in_progress'])])
            ->orderBy('active_count')
            ->orderByDesc('rating')
            ->first();
    }
}

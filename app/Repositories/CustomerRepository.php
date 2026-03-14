<?php

namespace App\Repositories;

use App\Models\Customer;
use Illuminate\Pagination\LengthAwarePaginator;

class CustomerRepository extends BaseRepository
{
    public function __construct(Customer $model)
    {
        parent::__construct($model);
    }

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return Customer::query()
            ->when($filters['search'] ?? null, fn($q, $s) =>
                $q->whereHas('user', fn($u) =>
                    $u->where('name', 'like', "%{$s}%")
                      ->orWhere('email', 'like', "%{$s}%")))
            ->with('user')
            ->withCount('bookings')
            ->latest()
            ->paginate($perPage);
    }
}

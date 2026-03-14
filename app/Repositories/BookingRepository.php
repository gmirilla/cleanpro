<?php

namespace App\Repositories;

use App\Models\Booking;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class BookingRepository extends BaseRepository
{
    public function __construct(Booking $model)
    {
        parent::__construct($model);
    }

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return Booking::query()
            ->when($filters['search'] ?? null, fn($q, $s) =>
                $q->where('booking_reference', 'like', "%{$s}%"))
            ->when($filters['status'] ?? null, fn($q, $s) =>
                $q->where('status', $s))
            ->when($filters['date'] ?? null, fn($q, $d) =>
                $q->whereDate('service_date', $d))
            ->when($filters['staff_id'] ?? null, fn($q, $id) =>
                $q->where('assigned_staff_id', $id))
            ->when($filters['customer_id'] ?? null, fn($q, $id) =>
                $q->where('customer_id', $id))
            ->with(['customer.user', 'items.service', 'assignedStaff.user'])
            ->latest()
            ->paginate($perPage);
    }

    public function forCalendar(string $year, string $month): Collection
    {
        return Booking::query()
            ->whereYear('service_date', $year)
            ->whereMonth('service_date', $month)
            ->with(['customer.user', 'items.service', 'assignedStaff.user'])
            ->get();
    }

    public function forStaff(int $staffId, array $filters = []): LengthAwarePaginator
    {
        return Booking::query()
            ->where('assigned_staff_id', $staffId)
            ->when($filters['status'] ?? null, fn($q, $s) => $q->where('status', $s))
            ->with(['customer.user', 'items.service', 'address', 'photos'])
            ->orderBy('service_date')
            ->paginate(10);
    }

    public function forCustomer(int $customerId): LengthAwarePaginator
    {
        return Booking::query()
            ->where('customer_id', $customerId)
            ->with(['items.service', 'invoice', 'assignedStaff.user'])
            ->latest()
            ->paginate(10);
    }

    public function getRevenueByMonth(int $months = 6): array
    {
        return Booking::query()
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subMonths($months))
            ->selectRaw("DATE_FORMAT(service_date, '%Y-%m') as month, SUM(total_amount) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();
    }
}

<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Staff;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    // Cache TTL constants — tweak as needed
    private const ADMIN_CACHE_TTL    = 180;  // 3 minutes
    private const CUSTOMER_CACHE_TTL = 60;   // 1 minute
    private const STAFF_CACHE_TTL    = 30;   // 30 seconds (task board updates frequently)

    // ──────────────────────────────────────────────────────────────
    // Admin Stats
    // ──────────────────────────────────────────────────────────────

    public function getAdminStats(): array
    {
        return Cache::remember('admin_dashboard_stats', self::ADMIN_CACHE_TTL, function () {
            // 1 query instead of 4 separate Booking::count() calls
            $bookingCounts = Booking::selectRaw("
                COUNT(*)                                                        AS total,
                SUM(status = 'pending')                                         AS pending,
                SUM(status IN ('confirmed','assigned','in_progress'))           AS active,
                SUM(status = 'completed' AND DATE(completed_at) = CURDATE())   AS completed_today
            ")->first();

            // 1 query instead of 2 separate Staff::count() calls
            $staffCounts = Staff::selectRaw("
                COUNT(*)                                         AS total,
                SUM(availability_status = 'available')           AS available
            ")->first();

            // 1 query instead of 2 separate Payment::sum() calls
            $revenueCounts = Payment::where('payment_status', 'completed')
                ->selectRaw("
                    SUM(amount)                                                      AS total_revenue,
                    SUM(CASE WHEN MONTH(paid_at) = ? AND YEAR(paid_at) = ? THEN amount ELSE 0 END) AS monthly_revenue
                ", [now()->month, now()->year])
                ->first();

            return [
                'total_bookings'    => (int) $bookingCounts->total,
                'pending_bookings'  => (int) $bookingCounts->pending,
                'active_jobs'       => (int) $bookingCounts->active,
                'completed_today'   => (int) $bookingCounts->completed_today,
                'total_customers'   => Customer::count(),
                'total_staff'       => (int) $staffCounts->total,
                'available_staff'   => (int) $staffCounts->available,
                'monthly_revenue'   => (float) $revenueCounts->monthly_revenue,
                'total_revenue'     => (float) $revenueCounts->total_revenue,
                'unpaid_invoices'   => Invoice::where('status', 'unpaid')->count(),
                'revenue_chart'     => $this->revenueChart(),
                'top_services'      => $this->topServices(),
                'staff_performance' => $this->staffPerformance(),
            ];
        });
    }

    /**
     * Call this after any booking/payment mutation so the cache doesn't go stale.
     */
    public function flushAdminCache(): void
    {
        Cache::forget('admin_dashboard_stats');
    }

    // ──────────────────────────────────────────────────────────────
    // Staff Stats
    // ──────────────────────────────────────────────────────────────

    public function getStaffStats(?int $staffId): array
    {
        if (! $staffId) {
            return ['today_jobs' => 0, 'upcoming_jobs' => 0, 'completed_total' => 0, 'in_progress' => 0];
        }

        return Cache::remember("staff_dashboard_stats_{$staffId}", self::STAFF_CACHE_TTL, function () use ($staffId) {
            // 1 query instead of 4 separate count() calls
            $counts = Booking::where('assigned_staff_id', $staffId)
                ->selectRaw("
                    SUM(DATE(service_date) = CURDATE())                                      AS today_jobs,
                    SUM(status IN ('assigned','confirmed') AND service_date > NOW())         AS upcoming_jobs,
                    SUM(status = 'completed')                                                AS completed_total,
                    SUM(status = 'in_progress')                                              AS in_progress
                ")
                ->first();

            return [
                'today_jobs'      => (int) $counts->today_jobs,
                'upcoming_jobs'   => (int) $counts->upcoming_jobs,
                'completed_total' => (int) $counts->completed_total,
                'in_progress'     => (int) $counts->in_progress,
            ];
        });
    }

    public function flushStaffCache(int $staffId): void
    {
        Cache::forget("staff_dashboard_stats_{$staffId}");
    }

    // ──────────────────────────────────────────────────────────────
    // Customer Stats
    // ──────────────────────────────────────────────────────────────

    public function getCustomerStats(?int $customerId): array
    {
        if (! $customerId) {
            return ['total_bookings' => 0, 'completed' => 0, 'upcoming' => 0, 'unpaid_invoices' => 0];
        }

        return Cache::remember("customer_dashboard_stats_{$customerId}", self::CUSTOMER_CACHE_TTL, function () use ($customerId) {
            // 1 query instead of 3 separate Booking::count() calls
            $bookingCounts = Booking::where('customer_id', $customerId)
                ->selectRaw("
                    COUNT(*)                                                                         AS total,
                    SUM(status = 'completed')                                                        AS completed,
                    SUM(status IN ('pending','confirmed','assigned') AND service_date > NOW())       AS upcoming
                ")
                ->first();

            // JOIN instead of whereHas (avoids correlated subquery)
            $unpaidInvoices = Invoice::join('bookings', 'bookings.id', '=', 'invoices.booking_id')
                ->where('bookings.customer_id', $customerId)
                ->where('invoices.status', 'unpaid')
                ->count();

            return [
                'total_bookings'  => (int) $bookingCounts->total,
                'completed'       => (int) $bookingCounts->completed,
                'upcoming'        => (int) $bookingCounts->upcoming,
                'unpaid_invoices' => $unpaidInvoices,
            ];
        });
    }

    public function flushCustomerCache(int $customerId): void
    {
        Cache::forget("customer_dashboard_stats_{$customerId}");
    }

    // ──────────────────────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────────────────────

    /**
     * Aggregate revenue by month entirely in the DB — no more ->get() + PHP groupBy.
     */
    private function revenueChart(int $months = 6): array
    {
        return Payment::where('payment_status', 'completed')
            ->where('paid_at', '>=', now()->subMonths($months)->startOfMonth())
            ->selectRaw("
                DATE_FORMAT(paid_at, '%Y-%m')   AS month_key,
                DATE_FORMAT(paid_at, '%b %Y')   AS label,
                SUM(amount)                     AS total
            ")
            ->groupByRaw("DATE_FORMAT(paid_at, '%Y-%m'), DATE_FORMAT(paid_at, '%b %Y')")
            ->orderBy('month_key')
            ->pluck('total', 'label')
            ->toArray();
    }

    /**
     * Top services — already uses a DB join; no changes needed here.
     */
    private function topServices(int $limit = 5): array
    {
        return DB::table('booking_items')
            ->join('services', 'services.id', '=', 'booking_items.service_id')
            ->selectRaw('services.name, SUM(booking_items.quantity) AS total_booked')
            ->groupBy('services.id', 'services.name')
            ->orderByDesc('total_booked')
            ->limit($limit)
            ->get()
            ->map(fn ($r) => ['name' => $r->name, 'total_booked' => $r->total_booked])
            ->toArray();
    }

    /**
     * Staff performance — select only the columns we need, no extra hydration.
     */
    private function staffPerformance(int $limit = 5): array
    {
        return Staff::join('users', 'users.id', '=', 'staff.user_id')
            ->select(
                'users.name',
                'staff.rating',
                'staff.completed_jobs',
                'staff.availability_status'
            )
            ->orderByDesc('staff.rating')
            ->orderByDesc('staff.completed_jobs')
            ->limit($limit)
            ->get()
            ->map(fn ($s) => [
                'name'           => $s->name,
                'rating'         => $s->rating,
                'completed_jobs' => $s->completed_jobs,
                'availability'   => $s->availability_status,
            ])
            ->toArray();
    }
}

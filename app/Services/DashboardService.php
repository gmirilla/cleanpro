<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Staff;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getAdminStats(): array
    {
        return [
            'total_bookings'    => Booking::count(),
            'pending_bookings'  => Booking::where('status','pending')->count(),
            'active_jobs'       => Booking::whereIn('status',['confirmed','assigned','in_progress'])->count(),
            'completed_today'   => Booking::where('status','completed')->whereDate('completed_at',today())->count(),
            'total_customers'   => Customer::count(),
            'total_staff'       => Staff::count(),
            'available_staff'   => Staff::where('availability_status','available')->count(),
            'monthly_revenue'   => Payment::where('payment_status','completed')->whereMonth('paid_at',now()->month)->sum('amount'),
            'total_revenue'     => Payment::where('payment_status','completed')->sum('amount'),
            'unpaid_invoices'   => Invoice::where('status','unpaid')->count(),
            'revenue_chart'     => $this->revenueChart(),
            'top_services'      => $this->topServices(),
            'staff_performance' => $this->staffPerformance(),
        ];
    }

    public function getStaffStats(?int $staffId): array
    {
        if (!$staffId) {
            return ['today_jobs'=>0,'upcoming_jobs'=>0,'completed_total'=>0,'in_progress'=>0];
        }
        return [
            'today_jobs'      => Booking::where('assigned_staff_id',$staffId)->whereDate('service_date',today())->count(),
            'upcoming_jobs'   => Booking::where('assigned_staff_id',$staffId)->whereIn('status',['assigned','confirmed'])->where('service_date','>',now())->count(),
            'completed_total' => Booking::where('assigned_staff_id',$staffId)->where('status','completed')->count(),
            'in_progress'     => Booking::where('assigned_staff_id',$staffId)->where('status','in_progress')->count(),
        ];
    }

    public function getCustomerStats(?int $customerId): array
    {
        if (!$customerId) {
            return ['total_bookings'=>0,'completed'=>0,'upcoming'=>0,'unpaid_invoices'=>0];
        }
        return [
            'total_bookings'  => Booking::where('customer_id',$customerId)->count(),
            'completed'       => Booking::where('customer_id',$customerId)->where('status','completed')->count(),
            'upcoming'        => Booking::where('customer_id',$customerId)->whereIn('status',['pending','confirmed','assigned'])->where('service_date','>',now())->count(),
            'unpaid_invoices' => Invoice::whereHas('booking',fn($q)=>$q->where('customer_id',$customerId))->where('status','unpaid')->count(),
        ];
    }

    private function revenueChart(int $months=6): array
    {
        return Payment::where('payment_status', 'completed')
    ->where('paid_at', '>=', now()->subMonths($months)->startOfMonth())
    ->get()
    ->groupBy(function ($payment) {
        return $payment->paid_at->format('Y-m'); // grouping key
    })
    ->map(function ($group) {
        return [
            'label' => $group->first()->paid_at->format('M Y'),
            'total' => $group->sum('amount'),
        ];
    })
    ->sortKeys() // ensures chronological order
    ->pluck('total', 'label')
    ->toArray();
    }

    private function topServices(int $limit=5): array
    {
        return DB::table('booking_items')
            ->join('services','services.id','=','booking_items.service_id')
            ->selectRaw('services.name, SUM(booking_items.quantity) as total_booked')
            ->groupBy('services.id','services.name')
            ->orderByDesc('total_booked')
            ->limit($limit)->get()
            ->map(fn($r)=>['name'=>$r->name,'total_booked'=>$r->total_booked])
            ->toArray();
    }

    private function staffPerformance(int $limit=5): array
    {
        return Staff::with('user')
            ->orderByDesc('rating')
            ->orderByDesc('completed_jobs')
            ->limit($limit)->get()
            ->map(fn($s)=>[
                'name'           => $s->name,
                'rating'         => $s->rating,
                'completed_jobs' => $s->completed_jobs,
                'availability'   => $s->availability_status,
            ])->toArray();
    }
}

<?php

namespace App\Livewire\Admin;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Staff;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Reports')]
class ReportsDashboard extends Component
{
    public string $period     = 'month';
    public string $reportType = 'revenue';

    public function render()
    {
        $startDate = match($this->period) {
            'week'    => now()->startOfWeek(),
            'month'   => now()->startOfMonth(),
            'quarter' => now()->startOfQuarter(),
            'year'    => now()->startOfYear(),
            default   => now()->startOfMonth(),
        };

        $data = match($this->reportType) {
            'revenue'  => $this->revenueReport($startDate),
            'bookings' => $this->bookingsReport($startDate),
            'staff'    => $this->staffReport($startDate),
            'services' => $this->servicesReport($startDate),
            default    => [],
        };

        return view('livewire.admin.reports-dashboard', compact('data'));
    }

    private function revenueReport($start): array
    {
        return [
            'total'     => Payment::where('payment_status','completed')->where('paid_at','>=',$start)->sum('amount'),
            'count'     => Payment::where('payment_status','completed')->where('paid_at','>=',$start)->count(),
            'by_method' => Payment::where('payment_status','completed')->where('paid_at','>=',$start)
                ->selectRaw('payment_method, SUM(amount) as total')->groupBy('payment_method')
                ->pluck('total','payment_method')->toArray(),
            'trend'     => Payment::where('payment_status','completed')->where('paid_at','>=',now()->subMonths(6))
                ->selectRaw("DATE_FORMAT(paid_at,'%b') as month, SUM(amount) as total")
                ->groupBy(DB::raw("DATE_FORMAT(paid_at,'%Y-%m')"),'month')
                ->orderBy(DB::raw("DATE_FORMAT(paid_at,'%Y-%m')"))
                ->pluck('total','month')->toArray(),
        ];
    }

    private function bookingsReport($start): array
    {
        return [
            'total'     => Booking::where('created_at','>=',$start)->count(),
            'completed' => Booking::where('status','completed')->where('created_at','>=',$start)->count(),
            'cancelled' => Booking::where('status','cancelled')->where('created_at','>=',$start)->count(),
            'by_status' => Booking::where('created_at','>=',$start)
                ->selectRaw('status, COUNT(*) as count')->groupBy('status')
                ->pluck('count','status')->toArray(),
        ];
    }

    private function staffReport($start): array
    {
        return Staff::with('user')
            ->withCount(['bookings as jobs_count'=>fn($q)=>$q->where('status','completed')->where('completed_at','>=',$start)])
            ->orderByDesc('jobs_count')->get()
            ->map(fn($s)=>['name'=>$s->name,'jobs'=>$s->jobs_count,'rating'=>$s->rating,'availability'=>$s->availability_status])
            ->toArray();
    }

    private function servicesReport($start): array
    {
        return DB::table('booking_items')
            ->join('services','services.id','=','booking_items.service_id')
            ->join('bookings','bookings.id','=','booking_items.booking_id')
            ->where('bookings.created_at','>=',$start)
            ->selectRaw('services.name, services.category, SUM(booking_items.quantity) as times_booked, SUM(booking_items.subtotal) as revenue')
            ->groupBy('services.id','services.name','services.category')
            ->orderByDesc('times_booked')->get()->toArray();
    }
}

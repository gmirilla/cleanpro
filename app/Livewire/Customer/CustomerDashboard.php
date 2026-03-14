<?php

namespace App\Livewire\Customer;

use App\Models\Booking;
use App\Models\Invoice;
use App\Services\DashboardService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.customer')]
#[Title('Dashboard')]
class CustomerDashboard extends Component
{
    public function render(DashboardService $dashboardService)
    {
        $customer = auth()->user()->customer;

        if (!$customer) {
            return view('livewire.customer.no-profile');
        }

        $stats = $dashboardService->getCustomerStats($customer->id);

        $recentBookings = Booking::where('customer_id',$customer->id)
            ->with(['items.service','assignedStaff.user'])
            ->latest()->limit(5)->get();

        $pendingInvoices = Invoice::whereHas('booking',fn($q)=>$q->where('customer_id',$customer->id))
            ->where('status','unpaid')
            ->with('booking.items.service')
            ->latest()->limit(3)->get();

        $notifications = auth()->user()->unreadNotifications()->limit(5)->get();

        return view('livewire.customer.customer-dashboard',
            compact('stats','recentBookings','pendingInvoices','notifications'));
    }
}

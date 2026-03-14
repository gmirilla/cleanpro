<?php

namespace App\Livewire\Admin;

use App\Models\Booking;
use App\Models\Staff;
use App\Repositories\BookingRepository;
use App\Services\BookingService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Bookings')]
class BookingManager extends Component
{
    use WithPagination;

    public string $search        = '';
    public string $statusFilter  = '';
    public string $dateFilter    = '';
    public ?int   $staffFilter   = null;
    public ?int   $assigningId   = null;
    public ?int   $selectedStaff = null;
    public ?int   $cancellingId  = null;
    public string $cancellationReason = '';

    public function updatedSearch(): void       { $this->resetPage(); }
    public function updatedStatusFilter(): void  { $this->resetPage(); }

    public function openAssign(int $bookingId): void
    {
        $this->assigningId   = $bookingId;
        $this->selectedStaff = null;
    }

    public function confirmAssign(BookingService $bookingService): void
    {
        $this->validate(['selectedStaff' => 'required|exists:staff,id']);
        $booking = Booking::findOrFail($this->assigningId);
        $staff   = Staff::findOrFail($this->selectedStaff);
        $bookingService->assignStaff($booking, $staff);
        $this->assigningId = null;
        $this->dispatch('notify', type: 'success', message: 'Staff assigned.');
    }

    public function updateStatus(int $id, string $status, BookingService $bookingService): void
    {
        $booking = Booking::findOrFail($id);
        $bookingService->updateStatus($booking, $status);
        $this->dispatch('notify', type: 'success', message: "Status updated to {$status}.");
    }

    public function openCancel(int $id): void
    {
        $this->cancellingId       = $id;
        $this->cancellationReason = '';
    }

    public function confirmCancel(BookingService $bookingService): void
    {
        $booking = Booking::findOrFail($this->cancellingId);
        $bookingService->cancel($booking, $this->cancellationReason);
        $this->cancellingId = null;
        $this->dispatch('notify', type: 'success', message: 'Booking cancelled.');
    }

    public function render(BookingRepository $repo)
    {
        $bookings = $repo->paginate(15, [
            'search'   => $this->search,
            'status'   => $this->statusFilter,
            'date'     => $this->dateFilter,
            'staff_id' => $this->staffFilter,
        ]);

        $availableStaff = Staff::available()->with('user')->get();

        return view('livewire.admin.booking-manager', compact('bookings', 'availableStaff'));
    }
}

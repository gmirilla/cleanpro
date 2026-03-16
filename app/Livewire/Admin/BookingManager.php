<?php

namespace App\Livewire\Admin;

use App\Models\Booking;
use App\Models\Staff;
use App\Repositories\BookingRepository;
use App\Services\BookingService;
use App\Services\InvoiceService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Bookings')]
class BookingManager extends Component
{
    use WithPagination;

    // ── Filters ──────────────────────────────────────────────────
    public string $search       = '';
    public string $statusFilter = '';
    public string $dateFilter   = '';
    public ?int   $staffFilter  = null;

    // ── Booking Detail Drawer ────────────────────────────────────
    public ?int $viewingId = null;

    // ── Assign Staff Modal ───────────────────────────────────────
    public ?int $assigningId   = null;
    public ?int $selectedStaff = null;

    // ── Reject / Cancel Modal ────────────────────────────────────
    public ?int   $rejectingId        = null;
    public string $cancellationReason = '';

    // ── Confirm Modal ────────────────────────────────────────────
    public ?int $confirmingId = null;

    // ── Generate Invoice Confirmation ────────────────────────────
    public ?int $generatingInvoiceId = null;

    public function updatedSearch(): void      { $this->resetPage(); }
    public function updatedStatusFilter(): void { $this->resetPage(); }

    // ── View booking detail ──────────────────────────────────────

    public function viewBooking(int $id): void
    {
        $this->viewingId = $id;
        // Close any other open modals
        $this->closeModals(exceptViewing: true);
    }

    public function closeDrawer(): void
    {
        $this->viewingId = null;
    }

    // ── Confirm booking ──────────────────────────────────────────

    public function openConfirm(int $id): void
    {
        $this->confirmingId = $id;
        $this->closeModals(exceptConfirm: true);
    }

    public function confirmBooking(BookingService $bookingService): void
    {
        $booking = Booking::findOrFail($this->confirmingId);
        $bookingService->updateStatus($booking, 'confirmed');
        $this->confirmingId = null;
        $this->refreshViewing($booking->id);
        $this->dispatch('notify', type: 'success', message: 'Booking confirmed. Invoice will be generated shortly.');
    }

    // ── Reject booking ───────────────────────────────────────────

    public function openReject(int $id): void
    {
        $this->rejectingId        = $id;
        $this->cancellationReason = '';
        $this->closeModals(exceptReject: true);
    }

    public function confirmReject(BookingService $bookingService): void
    {
        $this->validate(['cancellationReason' => 'nullable|string|max:500']);
        $booking = Booking::findOrFail($this->rejectingId);
        $bookingService->cancel($booking, $this->cancellationReason);
        $this->rejectingId = null;

        // If the drawer was showing this booking, close it
        if ($this->viewingId === $booking->id) {
            $this->viewingId = null;
        }

        $this->dispatch('notify', type: 'success', message: 'Booking rejected.');
    }

    // ── Assign staff ─────────────────────────────────────────────

    public function openAssign(int $bookingId): void
    {
        $this->assigningId   = $bookingId;
        $this->selectedStaff = null;
        $this->closeModals(exceptAssign: true);
    }

    public function confirmAssign(BookingService $bookingService): void
    {
        $this->validate(['selectedStaff' => 'required|exists:staff,id']);
        $booking = Booking::findOrFail($this->assigningId);
        $staff   = Staff::findOrFail($this->selectedStaff);
        $bookingService->assignStaff($booking, $staff);
        $this->assigningId = null;
        $this->refreshViewing($booking->id);
        $this->dispatch('notify', type: 'success', message: 'Staff assigned successfully.');
    }

    // ── Generate Invoice ─────────────────────────────────────────

    public function openGenerateInvoice(int $id): void
    {
        $this->generatingInvoiceId = $id;
        $this->closeModals(exceptInvoice: true);
    }

    public function generateInvoice(InvoiceService $invoiceService): void
    {
        $booking = Booking::with('invoice')->findOrFail($this->generatingInvoiceId);

        if ($booking->invoice) {
            $this->dispatch('notify', type: 'warning', message: 'An invoice already exists for this booking.');
            $this->generatingInvoiceId = null;
            return;
        }

        if (!in_array($booking->status, ['confirmed', 'assigned', 'in_progress', 'completed'])) {
            $this->dispatch('notify', type: 'error', message: 'Invoice can only be generated for confirmed or active bookings.');
            $this->generatingInvoiceId = null;
            return;
        }

        $invoiceService->generateFromBooking($booking);
        $this->generatingInvoiceId = null;
        $this->refreshViewing($booking->id);
        $this->dispatch('notify', type: 'success', message: 'Invoice generated and sent to customer.');
    }

    // ── Generic status update (in_progress, completed) ───────────

    public function updateStatus(int $id, string $status, BookingService $bookingService): void
    {
        $booking = Booking::findOrFail($id);
        $bookingService->updateStatus($booking, $status);
        $this->refreshViewing($id);
        $this->dispatch('notify', type: 'success', message: "Status updated to " . ucfirst(str_replace('_', ' ', $status)) . ".");
    }

    // ── Helpers ──────────────────────────────────────────────────

    /**
     * Refresh the viewing booking if it matches the given id.
     * Livewire will re-render automatically; this is a no-op but kept
     * to signal intent clearly.
     */
    private function refreshViewing(int $id): void
    {
        // If the drawer is open on this booking, Livewire re-renders will
        // automatically reload the viewingBooking via render().
    }

    /**
     * Close all modals except the specified one.
     */
    private function closeModals(
        bool $exceptViewing  = false,
        bool $exceptConfirm  = false,
        bool $exceptReject   = false,
        bool $exceptAssign   = false,
        bool $exceptInvoice  = false,
    ): void {
        if (!$exceptConfirm)  $this->confirmingId        = null;
        if (!$exceptReject)   { $this->rejectingId = null; $this->cancellationReason = ''; }
        if (!$exceptAssign)   { $this->assigningId = null; $this->selectedStaff = null; }
        if (!$exceptInvoice)  $this->generatingInvoiceId = null;
    }

    // ── Render ───────────────────────────────────────────────────

    public function render(BookingRepository $repo)
    {
        $bookings = $repo->paginate(15, [
            'search'   => $this->search,
            'status'   => $this->statusFilter,
            'date'     => $this->dateFilter,
            'staff_id' => $this->staffFilter,
        ]);

        $availableStaff = Staff::available()->with('user')->get();

        $viewingBooking = $this->viewingId
            ? Booking::with([
                'customer.user',
                'items.service',
                'assignedStaff.user',
                'invoice.payment',
                'address',
              ])->find($this->viewingId)
            : null;

        return view('livewire.admin.booking-manager', compact(
            'bookings',
            'availableStaff',
            'viewingBooking',
        ));
    }
}

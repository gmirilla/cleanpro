<?php

namespace App\Services;

use App\Events\BookingCreated;
use App\Events\BookingStatusChanged;
use App\Jobs\AssignStaffJob;
use App\Jobs\GenerateInvoiceJob;
use App\Jobs\SendBookingReminderJob;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Staff;
use App\Repositories\BookingRepository;
use App\Repositories\StaffRepository;
use Illuminate\Support\Facades\DB;

class BookingService
{
    public function __construct(
        private BookingRepository $bookingRepo,
        private StaffRepository   $staffRepo,
    ) {}

    /**
     * Create a new booking with items.
     */
    public function create(array $data): Booking
    {
        return DB::transaction(function () use ($data) {
            $booking = Booking::create([
                'customer_id'  => $data['customer_id'],
                'address_id'   => $data['address_id'] ?? null,
                'service_date' => $data['service_date'],
                'pickup_date'  => $data['pickup_date'] ?? null,
                'notes'        => $data['notes'] ?? null,
                'status'       => 'pending',
            ]);

            foreach ($data['items'] as $item) {
                BookingItem::create([
                    'booking_id' => $booking->id,
                    'service_id' => $item['service_id'],
                    'price'      => $item['price'],
                    'quantity'   => $item['quantity'],
                    'subtotal'   => $item['price'] * $item['quantity'],
                ]);
            }

            $booking->recalculateTotal();

            // Reminder 24 h before service date
            SendBookingReminderJob::dispatch($booking)
                ->delay($booking->service_date->subHours(24));

            event(new BookingCreated($booking));

            return $booking->fresh(['items.service', 'customer.user']);
        });
    }

    /**
     * Update booking status with appropriate side-effects.
     *
     * Invoice generation timing:
     *   - 'confirmed' → generate invoice immediately so customer can pay
     *   - 'completed' → if no invoice yet (e.g. cash jobs), generate one now
     */
    public function updateStatus(Booking $booking, string $status, array $extra = []): void
    {
        $updates = array_merge(['status' => $status], $extra);

        if ($status === 'confirmed') {
            $updates['confirmed_at'] = now();

            // ── Generate invoice at confirmation so customer can pay ──
            // Dispatched with a small delay so the DB transaction above commits first
            GenerateInvoiceJob::dispatch($booking)->delay(now()->addSeconds(3));

            // Auto-assign staff once booking is confirmed
            AssignStaffJob::dispatch($booking)->delay(now()->addSeconds(5));
        }

        if ($status === 'completed') {
            $updates['completed_at'] = now();

            // Safety net: generate invoice if one doesn't exist yet
            // (e.g. cash bookings that bypassed online payment)
            if (!$booking->invoice) {
                GenerateInvoiceJob::dispatch($booking)->delay(now()->addSeconds(3));
            }
        }

        $booking->update($updates);

        event(new BookingStatusChanged($booking, $status));
    }

    public function assignStaff(Booking $booking, Staff $staff): void
    {
        DB::transaction(function () use ($booking, $staff) {
            $booking->update([
                'assigned_staff_id' => $staff->id,
                'status'            => 'assigned',
            ]);
            $staff->update(['availability_status' => 'busy']);
        });

        event(new BookingStatusChanged($booking, 'assigned'));
    }

    public function cancel(Booking $booking, string $reason = ''): void
    {
        DB::transaction(function () use ($booking, $reason) {
            $booking->update([
                'status'              => 'cancelled',
                'cancellation_reason' => $reason,
            ]);

            if ($booking->assignedStaff) {
                $booking->assignedStaff->update(['availability_status' => 'available']);
            }

            // Cancel the invoice if it exists and hasn't been paid
            if ($booking->invoice && !$booking->invoice->isPaid()) {
                $booking->invoice->update(['status' => 'cancelled']);
            }
        });

        event(new BookingStatusChanged($booking, 'cancelled'));
    }

    public function findBestAvailableStaff(Booking $booking): ?Staff
    {
        return $this->staffRepo->findAvailable();
    }
}

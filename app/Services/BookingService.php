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
     *
     * @param  array{customer_id:int, address_id:?int, service_date:string,
     *               notes:?string, items:array<array{service_id:int, quantity:int, price:float}>} $data
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

            AssignStaffJob::dispatch($booking)->delay(now()->addSeconds(10));

            // Reminder 24 h before service
            SendBookingReminderJob::dispatch($booking)
                ->delay($booking->service_date->subHours(24));

            event(new BookingCreated($booking));

            return $booking->fresh(['items.service', 'customer.user']);
        });
    }

    public function updateStatus(Booking $booking, string $status, array $extra = []): void
    {
        $updates = array_merge(['status' => $status], $extra);

        if ($status === 'confirmed') {
            $updates['confirmed_at'] = now();
        }

        if ($status === 'completed') {
            $updates['completed_at'] = now();
            GenerateInvoiceJob::dispatch($booking);
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
        });

        event(new BookingStatusChanged($booking, 'cancelled'));
    }

    public function findBestAvailableStaff(Booking $booking): ?Staff
    {
        return $this->staffRepo->findAvailable();
    }
}

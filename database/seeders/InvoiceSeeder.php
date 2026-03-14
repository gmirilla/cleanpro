<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Invoice;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        $completedBookings = Booking::where('status', 'completed')
            ->doesntHave('invoice')
            ->get();

        foreach ($completedBookings as $booking) {
            $amount   = $booking->total_amount;
            $tax      = round($amount * 0.075, 2);
            $total    = $amount + $tax;
            $isPaid   = fake()->boolean(75);

            Invoice::create([
                'booking_id'     => $booking->id,
                'invoice_number' => 'INV-' . date('Y') . '-' . strtoupper(Str::random(6)),
                'amount'         => $amount,
                'tax'            => $tax,
                'discount'       => 0,
                'total'          => $total,
                'status'         => $isPaid ? 'paid' : 'unpaid',
                'due_date'       => $booking->completed_at?->addDays(7) ?? now()->addDays(7),
                'paid_at'        => $isPaid ? $booking->completed_at?->addDays(rand(1, 5)) : null,
            ]);
        }

        // A few unpaid invoices for confirmed bookings (not yet completed)
        Booking::whereIn('status', ['confirmed', 'assigned'])
            ->doesntHave('invoice')
            ->limit(5)
            ->get()
            ->each(function ($booking) {
                $amount = $booking->total_amount;
                $tax    = round($amount * 0.075, 2);

                Invoice::create([
                    'booking_id'     => $booking->id,
                    'invoice_number' => 'INV-' . date('Y') . '-' . strtoupper(Str::random(6)),
                    'amount'         => $amount,
                    'tax'            => $tax,
                    'discount'       => 0,
                    'total'          => $amount + $tax,
                    'status'         => 'unpaid',
                    'due_date'       => now()->addDays(7),
                    'paid_at'        => null,
                ]);
            });

        $this->command->info('✅ ' . Invoice::count() . ' invoices seeded.');
    }
}

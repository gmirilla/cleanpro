<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Customer;
use App\Models\LaundryItem;
use App\Models\LaundryOrder;
use App\Models\Service;
use App\Models\Staff;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        $customers      = Customer::with('addresses')->get();
        $services       = Service::all()->keyBy('id');
        $staffMembers   = Staff::all();

        if ($customers->isEmpty() || $services->isEmpty()) {
            $this->command->warn('Skipping BookingSeeder – no customers or services found.');
            return;
        }

        $statuses = ['pending', 'confirmed', 'assigned', 'in_progress', 'completed', 'completed', 'completed', 'cancelled'];

        for ($i = 0; $i < 40; $i++) {
            $customer = $customers->random();
            $address  = $customer->addresses->first();
            $status   = fake()->randomElement($statuses);
            $staff    = ($status !== 'pending') ? $staffMembers->random() : null;

            $serviceDate = match ($status) {
                'completed', 'cancelled' => now()->subDays(rand(1, 60)),
                'in_progress'            => now()->subHours(rand(1, 4)),
                default                  => now()->addDays(rand(1, 14)),
            };

            $booking = Booking::create([
                'customer_id'       => $customer->id,
                'address_id'        => $address?->id,
                'assigned_staff_id' => $staff?->id,
                'booking_reference' => 'BK-' . strtoupper(Str::random(8)),
                'service_date'      => $serviceDate,
                'status'            => $status,
                'notes'             => fake()->optional(0.3)->sentence(),
                'confirmed_at'      => in_array($status, ['confirmed','assigned','in_progress','completed']) ? $serviceDate->clone()->subDay() : null,
                'completed_at'      => $status === 'completed' ? $serviceDate->clone()->addHours(2) : null,
            ]);

            // Add 1–3 booking items
            $selectedServices = $services->random(rand(1, 3));
            $total = 0;

            foreach ($selectedServices as $svc) {
                $qty      = rand(1, 2);
                $subtotal = $svc->base_price * $qty;
                $total   += $subtotal;

                BookingItem::create([
                    'booking_id' => $booking->id,
                    'service_id' => $svc->id,
                    'price'      => $svc->base_price,
                    'quantity'   => $qty,
                    'subtotal'   => $subtotal,
                ]);

                // Laundry order for laundry services
                if ($svc->category === 'laundry' && !$booking->laundryOrder) {
                    $order = LaundryOrder::create([
                        'booking_id'           => $booking->id,
                        'weight'               => fake()->randomFloat(1, 1, 10),
                        'garment_count'        => rand(3, 20),
                        'detergent_type'       => fake()->randomElement(['standard', 'hypoallergenic', 'eco']),
                        'special_instructions' => fake()->optional(0.3)->sentence(),
                        'express_service'      => fake()->boolean(20),
                    ]);

                    // Add garment items
                    $garmentCount = rand(2, 5);
                    for ($g = 0; $g < $garmentCount; $g++) {
                        LaundryItem::create([
                            'laundry_order_id' => $order->id,
                            'garment_type'     => fake()->randomElement(\App\Models\LaundryItem::$garmentTypes),
                            'quantity'         => rand(1, 5),
                            'status'           => $status === 'completed'
                                ? fake()->randomElement(['ready', 'delivered'])
                                : fake()->randomElement(['received', 'washing', 'drying', 'ironing']),
                        ]);
                    }
                }
            }

            $booking->update(['total_amount' => $total]);
        }

        $this->command->info('✅ ' . Booking::count() . ' bookings seeded.');
    }
}

<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        $serviceDate = fake()->dateTimeBetween('+1 day', '+30 days');

        return [
            'customer_id'       => Customer::factory(),
            'booking_reference' => 'BK-' . strtoupper(Str::random(8)),
            'service_date'      => $serviceDate,
            'status'            => fake()->randomElement(['pending', 'confirmed', 'assigned', 'completed']),
            'total_amount'      => fake()->randomFloat(2, 3000, 50000),
            'notes'             => fake()->optional(0.4)->sentence(),
        ];
    }

    public function pending(): static   { return $this->state(['status' => 'pending']); }
    public function confirmed(): static { return $this->state(['status' => 'confirmed', 'confirmed_at' => now()]); }
    public function completed(): static {
        return $this->state([
            'status'        => 'completed',
            'confirmed_at'  => now()->subDays(2),
            'completed_at'  => now()->subDay(),
            'service_date'  => now()->subDay(),
        ]);
    }
    public function cancelled(): static { return $this->state(['status' => 'cancelled', 'cancellation_reason' => 'Customer request']); }
}

<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        $amount   = fake()->randomFloat(2, 3000, 50000);
        $tax      = round($amount * 0.075, 2);
        $discount = 0;
        $total    = $amount + $tax;

        return [
            'booking_id'     => Booking::factory(),
            'invoice_number' => 'INV-' . date('Y') . '-' . strtoupper(Str::random(6)),
            'amount'         => $amount,
            'tax'            => $tax,
            'discount'       => $discount,
            'total'          => $total,
            'status'         => fake()->randomElement(['unpaid', 'paid', 'paid', 'paid']),
            'due_date'       => fake()->dateTimeBetween('now', '+14 days'),
        ];
    }

    public function paid(): static
    {
        return $this->state([
            'status'  => 'paid',
            'paid_at' => now()->subDays(fake()->numberBetween(1, 30)),
        ]);
    }

    public function unpaid(): static
    {
        return $this->state(['status' => 'unpaid', 'paid_at' => null]);
    }
}

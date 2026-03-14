<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        $booking = Booking::factory()->completed()->create();
        $invoice = Invoice::factory()->paid()->create(['booking_id' => $booking->id]);

        return [
            'booking_id'            => $booking->id,
            'invoice_id'            => $invoice->id,
            'amount'                => $invoice->total,
            'currency'              => 'NGN',
            'payment_method'        => fake()->randomElement(['paystack', 'cash', 'bank_transfer']),
            'payment_status'        => 'completed',
            'transaction_reference' => 'PAY-' . strtoupper(fake()->bothify('??######')),
            'paid_at'               => fake()->dateTimeBetween('-60 days', 'now'),
        ];
    }
}

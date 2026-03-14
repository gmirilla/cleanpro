<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $paidInvoices = Invoice::where('status', 'paid')
            ->doesntHave('payment')
            ->with('booking')
            ->get();

        foreach ($paidInvoices as $invoice) {
            Payment::create([
                'booking_id'            => $invoice->booking_id,
                'invoice_id'            => $invoice->id,
                'amount'                => $invoice->total,
                'currency'              => 'NGN',
                'payment_method'        => fake()->randomElement(['paystack', 'paystack', 'cash', 'bank_transfer']),
                'payment_status'        => 'completed',
                'transaction_reference' => 'PAY-' . strtoupper(fake()->bothify('??######??')),
                'gateway_reference'     => fake()->optional(0.7)->uuid(),
                'paid_at'               => $invoice->paid_at,
            ]);
        }

        $this->command->info('✅ ' . Payment::count() . ' payments seeded.');
    }
}

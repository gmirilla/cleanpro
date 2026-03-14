<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            ServiceSeeder::class,
            CustomerSeeder::class,
            StaffSeeder::class,
            BookingSeeder::class,
            InvoiceSeeder::class,
            PaymentSeeder::class,
        ]);
    }
}

<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        // Known demo customer
        $demoUser = User::factory()->customer()->create([
            'name'  => 'Demo Customer',
            'email' => 'customer@cleanpro.com',
        ]);

        $demoCustomer = Customer::create([
            'user_id' => $demoUser->id,
            'phone'   => '+234 801 234 5678',
        ]);

        Address::create([
            'customer_id' => $demoCustomer->id,
            'label'       => 'Home',
            'address'     => '15 Admiralty Way, Lekki Phase 1',
            'city'        => 'Lagos',
            'state'       => 'Lagos',
            'is_default'  => true,
        ]);

        Address::create([
            'customer_id' => $demoCustomer->id,
            'label'       => 'Office',
            'address'     => '7 Adeola Odeku Street, Victoria Island',
            'city'        => 'Lagos',
            'state'       => 'Lagos',
            'is_default'  => false,
        ]);

        // Additional random customers
        Customer::factory(15)->create()->each(function (Customer $customer) {
            // Default address
            Address::factory()->default()->create(['customer_id' => $customer->id]);

            // Some have a second address
            if (fake()->boolean(40)) {
                Address::factory()->create(['customer_id' => $customer->id]);
            }
        });

        $this->command->info('✅ ' . Customer::count() . ' customers seeded.');
    }
}

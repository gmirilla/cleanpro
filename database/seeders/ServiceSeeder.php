<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            // ── Cleaning ────────────────────────────────────────
            [
                'name'             => 'Standard House Cleaning',
                'category'         => 'cleaning',
                'description'      => 'Thorough cleaning of all rooms, surfaces, floors and bathrooms in your home.',
                'base_price'       => 8000.00,
                'duration_minutes' => 120,
                'sort_order'       => 1,
            ],
            [
                'name'             => 'Office Cleaning',
                'category'         => 'cleaning',
                'description'      => 'Professional cleaning of office spaces, workstations, meeting rooms and common areas.',
                'base_price'       => 15000.00,
                'duration_minutes' => 180,
                'sort_order'       => 2,
            ],
            [
                'name'             => 'Deep Cleaning',
                'category'         => 'cleaning',
                'description'      => 'Intensive top-to-bottom cleaning including inside appliances, windows, and hard-to-reach areas.',
                'base_price'       => 20000.00,
                'duration_minutes' => 300,
                'sort_order'       => 3,
            ],
            [
                'name'             => 'Post-Construction Cleaning',
                'category'         => 'cleaning',
                'description'      => 'Specialised cleaning after renovation or construction to remove dust, debris and residue.',
                'base_price'       => 35000.00,
                'duration_minutes' => 480,
                'sort_order'       => 4,
            ],
            [
                'name'             => 'Move-In / Move-Out Cleaning',
                'category'         => 'cleaning',
                'description'      => 'Complete cleaning for tenants moving in or out to ensure the property is spotless.',
                'base_price'       => 25000.00,
                'duration_minutes' => 360,
                'sort_order'       => 5,
            ],
            [
                'name'             => 'Carpet & Upholstery Cleaning',
                'category'         => 'cleaning',
                'description'      => 'Steam cleaning of carpets, sofas and upholstered furniture to remove stains and odours.',
                'base_price'       => 12000.00,
                'duration_minutes' => 120,
                'sort_order'       => 6,
            ],

            // ── Laundry ─────────────────────────────────────────
            [
                'name'             => 'Wash & Fold',
                'category'         => 'laundry',
                'description'      => 'Clothes washed, dried and neatly folded. Charged per kilogram.',
                'base_price'       => 800.00,
                'duration_minutes' => 120,
                'sort_order'       => 1,
            ],
            [
                'name'             => 'Dry Cleaning',
                'category'         => 'laundry',
                'description'      => 'Professional dry cleaning for delicate garments, suits and special fabrics.',
                'base_price'       => 2500.00,
                'duration_minutes' => 240,
                'sort_order'       => 2,
            ],
            [
                'name'             => 'Ironing & Pressing',
                'category'         => 'laundry',
                'description'      => 'Professional ironing and pressing of your garments. Per item pricing.',
                'base_price'       => 300.00,
                'duration_minutes' => 60,
                'sort_order'       => 3,
            ],
            [
                'name'             => 'Pickup & Delivery Laundry',
                'category'         => 'laundry',
                'description'      => 'We pick up your laundry, clean it and deliver it back to your door.',
                'base_price'       => 5000.00,
                'duration_minutes' => 180,
                'sort_order'       => 4,
            ],
            [
                'name'             => 'Express Laundry (Same Day)',
                'category'         => 'laundry',
                'description'      => 'Same-day laundry service for urgent needs. Priority handling.',
                'base_price'       => 3500.00,
                'duration_minutes' => 120,
                'sort_order'       => 5,
            ],
        ];

        foreach ($services as $service) {
            Service::create(array_merge($service, ['status' => 'active']));
        }

        $this->command->info('✅ ' . count($services) . ' services seeded.');
    }
}

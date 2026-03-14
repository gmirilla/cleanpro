<?php

namespace Database\Seeders;

use App\Models\Staff;
use App\Models\User;
use Illuminate\Database\Seeder;

class StaffSeeder extends Seeder
{
    public function run(): void
    {
        $members = [
            ['name' => 'Emeka Johnson',   'email' => 'emeka@cleanpro.com',   'position' => 'Senior Cleaner',       'rating' => 4.8],
            ['name' => 'Amina Okafor',    'email' => 'amina@cleanpro.com',    'position' => 'Laundry Specialist',   'rating' => 4.7],
            ['name' => 'Chidi Nwosu',     'email' => 'chidi@cleanpro.com',     'position' => 'Deep Cleaning Expert', 'rating' => 4.5],
            ['name' => 'Fatima Bello',    'email' => 'fatima@cleanpro.com',    'position' => 'Cleaner',              'rating' => 4.2],
            ['name' => 'Segun Adeyemi',   'email' => 'segun@cleanpro.com',   'position' => 'Team Lead',             'rating' => 4.9],
            ['name' => 'Grace Eze',       'email' => 'grace@cleanpro.com',       'position' => 'Cleaner',              'rating' => 4.3],
            ['name' => 'Bola Adebayo',    'email' => 'bola@cleanpro.com',    'position' => 'Laundry Specialist',   'rating' => 4.6],
        ];

        foreach ($members as $i => $data) {
            $user = User::factory()->staff()->create([
                'name'  => $data['name'],
                'email' => $data['email'],
            ]);

            Staff::create([
                'user_id'             => $user->id,
                'phone'               => '+234 8' . rand(10, 99) . ' ' . rand(100, 999) . ' ' . rand(1000, 9999),
                'position'            => $data['position'],
                'availability_status' => $i < 5 ? 'available' : 'off_duty',
                'working_days'        => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
                'shift_start'         => '08:00',
                'shift_end'           => '17:00',
                'rating'              => $data['rating'],
                'completed_jobs'      => rand(20, 150),
            ]);
        }

        // Also seed the demo staff user
        $staffUser = User::factory()->staff()->create([
            'name'  => 'Demo Staff',
            'email' => 'staff@cleanpro.com',
        ]);

        Staff::create([
            'user_id'             => $staffUser->id,
            'phone'               => '+234 800 000 0001',
            'position'            => 'Cleaner',
            'availability_status' => 'available',
            'working_days'        => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'shift_start'         => '08:00',
            'shift_end'           => '17:00',
            'rating'              => 4.5,
            'completed_jobs'      => 42,
        ]);

        $this->command->info('✅ ' . Staff::count() . ' staff members seeded.');
    }
}

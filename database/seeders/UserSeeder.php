<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin
        User::factory()->superAdmin()->create([
            'name'  => 'Super Admin',
            'email' => 'superadmin@cleanpro.com',
        ]);

        // Company Admin
        User::factory()->admin()->create([
            'name'  => 'Company Admin',
            'email' => 'admin@cleanpro.com',
        ]);

        $this->command->info('✅ Admin users seeded.');
    }
}

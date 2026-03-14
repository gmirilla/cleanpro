<?php

namespace Database\Factories;

use App\Models\Staff;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StaffFactory extends Factory
{
    protected $model = Staff::class;

    public function definition(): array
    {
        return [
            'user_id'             => User::factory()->staff(),
            'phone'               => fake()->phoneNumber(),
            'position'            => fake()->randomElement(['Cleaner', 'Senior Cleaner', 'Laundry Specialist', 'Team Lead', 'Driver']),
            'availability_status' => fake()->randomElement(['available', 'available', 'available', 'busy']),
            'working_days'        => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'shift_start'         => '08:00',
            'shift_end'           => '17:00',
            'rating'              => fake()->randomFloat(2, 3.5, 5.0),
            'completed_jobs'      => fake()->numberBetween(0, 200),
        ];
    }

    public function available(): static
    {
        return $this->state(['availability_status' => 'available']);
    }
}

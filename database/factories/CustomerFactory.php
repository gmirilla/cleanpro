<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->customer(),
            'phone'   => fake()->phoneNumber(),
            'notes'   => fake()->optional(0.3)->sentence(),
        ];
    }
}

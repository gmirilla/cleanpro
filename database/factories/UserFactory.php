<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name'              => fake()->name(),
            'email'             => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password'          => bcrypt('password'),
            'role'              => 'customer',
            'is_active'         => true,
            'remember_token'    => Str::random(10),
        ];
    }

    public function superAdmin(): static
    {
        return $this->state(['role' => 'super_admin']);
    }

    public function admin(): static
    {
        return $this->state(['role' => 'admin']);
    }

    public function staff(): static
    {
        return $this->state(['role' => 'staff']);
    }

    public function customer(): static
    {
        return $this->state(['role' => 'customer']);
    }

    public function unverified(): static
    {
        return $this->state(['email_verified_at' => null]);
    }
}

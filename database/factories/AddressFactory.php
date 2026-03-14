<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class AddressFactory extends Factory
{
    protected $model = Address::class;

    private static array $nigerianStates = [
        'Lagos', 'Abuja', 'Kano', 'Ogun', 'Rivers', 'Anambra',
        'Oyo', 'Delta', 'Enugu', 'Kaduna',
    ];

    private static array $lagos_areas = [
        'Lekki', 'Victoria Island', 'Ikeja', 'Surulere', 'Ikoyi',
        'Yaba', 'Ajah', 'Ikorodu', 'Apapa', 'Maryland',
    ];

    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'label'       => fake()->randomElement(['Home', 'Office', 'Other']),
            'address'     => fake()->streetAddress() . ', ' . fake()->randomElement(self::$lagos_areas),
            'city'        => 'Lagos',
            'state'       => fake()->randomElement(self::$nigerianStates),
            'postal_code' => fake()->optional()->numerify('1####'),
            'is_default'  => false,
        ];
    }

    public function default(): static
    {
        return $this->state(['is_default' => true, 'label' => 'Home']);
    }
}

<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceFactory extends Factory
{
    protected $model = Service::class;

    private static array $cleaningServices = [
        ['name' => 'House Cleaning',           'duration' => 120],
        ['name' => 'Office Cleaning',          'duration' => 180],
        ['name' => 'Deep Cleaning',            'duration' => 240],
        ['name' => 'Post-Construction Cleaning','duration' => 360],
        ['name' => 'Move-In/Move-Out Cleaning','duration' => 300],
        ['name' => 'Carpet Cleaning',          'duration' => 90],
        ['name' => 'Window Cleaning',          'duration' => 60],
    ];

    private static array $laundryServices = [
        ['name' => 'Wash and Fold',           'duration' => 120],
        ['name' => 'Dry Cleaning',            'duration' => 240],
        ['name' => 'Ironing',                 'duration' => 60],
        ['name' => 'Pickup & Delivery',       'duration' => 180],
        ['name' => 'Express Laundry',         'duration' => 90],
        ['name' => 'Stain Removal',           'duration' => 60],
    ];

    public function definition(): array
    {
        $category = fake()->randomElement(['cleaning', 'laundry']);
        $pool     = $category === 'cleaning' ? self::$cleaningServices : self::$laundryServices;
        $svc      = fake()->randomElement($pool);

        return [
            'name'             => $svc['name'],
            'category'         => $category,
            'description'      => fake()->sentence(12),
            'base_price'       => fake()->randomFloat(2, 2000, 25000),
            'duration_minutes' => $svc['duration'],
            'status'           => 'active',
            'sort_order'       => fake()->numberBetween(0, 10),
        ];
    }

    public function cleaning(): static
    {
        $svc = fake()->randomElement(self::$cleaningServices);
        return $this->state([
            'category'         => 'cleaning',
            'name'             => $svc['name'],
            'duration_minutes' => $svc['duration'],
        ]);
    }

    public function laundry(): static
    {
        $svc = fake()->randomElement(self::$laundryServices);
        return $this->state([
            'category'         => 'laundry',
            'name'             => $svc['name'],
            'duration_minutes' => $svc['duration'],
        ]);
    }
}

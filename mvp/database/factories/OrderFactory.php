<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'store_id' => Store::factory(),
            'customer_name' => fake()->name(),
            'customer_phone' => '584' . fake()->numerify('#########'),
            'customer_notes' => fake()->optional()->sentence(),
            'status' => fake()->randomElement([
                'pending',
                'confirmed',
                'preparing',
                'ready',
                'completed',
                'cancelled',
            ]),
            'total' => fake()->randomFloat(2, 10, 1000),
        ];
    }
}

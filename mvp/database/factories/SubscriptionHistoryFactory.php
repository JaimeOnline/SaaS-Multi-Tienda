<?php

namespace Database\Factories;

use App\Models\SubscriptionHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SubscriptionHistory>
 */
class SubscriptionHistoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = SubscriptionHistory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statuses = ['trialing', 'expired', 'grace_period', 'suspended', 'active'];

        return [
            'subscription_id' => null,
            'from_status' => $this->faker->optional()->randomElement([null, ...$statuses]),
            'to_status' => $this->faker->randomElement($statuses),
            'reason' => $this->faker->optional()->sentence(),
            'origin' => $this->faker->randomElement(['system', 'user', 'webhook', 'admin']),
            'changed_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ];
    }
}

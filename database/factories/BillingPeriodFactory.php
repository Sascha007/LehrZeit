<?php

namespace Database\Factories;

use App\Models\BillingPeriod;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BillingPeriod>
 */
class BillingPeriodFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'month' => fake()->numberBetween(1, 12),
            'year' => fake()->numberBetween(2023, 2025),
            'status' => 'OPEN',
            'submitted_at' => null,
            'approved_at' => null,
            'exported_at' => null,
        ];
    }

    /**
     * Indicate that the billing period is submitted.
     */
    public function submitted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'SUBMITTED',
            'submitted_at' => now(),
        ]);
    }

    /**
     * Indicate that the billing period is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'APPROVED',
            'submitted_at' => now()->subDays(2),
            'approved_at' => now(),
        ]);
    }

    /**
     * Indicate that the billing period is exported.
     */
    public function exported(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'EXPORTED',
            'submitted_at' => now()->subDays(3),
            'approved_at' => now()->subDays(1),
            'exported_at' => now(),
        ]);
    }
}

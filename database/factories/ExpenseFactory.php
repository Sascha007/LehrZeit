<?php

namespace Database\Factories;

use App\Models\Expense;
use App\Models\BillingPeriod;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Expense>
 */
class ExpenseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'billing_period_id' => BillingPeriod::factory(),
            'user_id' => User::factory(),
            'date' => fake()->dateTimeBetween('-30 days', '+30 days')->format('Y-m-d'),
            'category' => fake()->randomElement(['Travel', 'Materials', 'Books', 'Software', 'Other']),
            'description' => fake()->sentence(),
            'amount' => fake()->randomFloat(2, 5, 500),
            'receipt_path' => null,
        ];
    }

    /**
     * Indicate that the expense has a receipt.
     */
    public function withReceipt(): static
    {
        return $this->state(fn (array $attributes) => [
            'receipt_path' => 'receipts/test-receipt.pdf',
        ]);
    }
}

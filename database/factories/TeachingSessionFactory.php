<?php

namespace Database\Factories;

use App\Models\TeachingSession;
use App\Models\BillingPeriod;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TeachingSession>
 */
class TeachingSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startHour = fake()->numberBetween(8, 16);
        $duration = fake()->randomElement([1, 1.5, 2, 2.5, 3]);
        
        return [
            'billing_period_id' => BillingPeriod::factory(),
            'user_id' => User::factory(),
            'date' => fake()->dateTimeBetween('-30 days', '+30 days')->format('Y-m-d'),
            'start_time' => sprintf('%02d:00', $startHour),
            'end_time' => sprintf('%02d:%02d', $startHour + floor($duration), ($duration - floor($duration)) * 60),
            'hours' => $duration,
            'subject' => fake()->randomElement(['Mathematics', 'Physics', 'Chemistry', 'Computer Science', 'Biology']),
            'description' => fake()->optional()->sentence(),
            'location' => fake()->optional()->randomElement(['Room 101', 'Room 202', 'Lab A', 'Online', 'Main Hall']),
        ];
    }
}

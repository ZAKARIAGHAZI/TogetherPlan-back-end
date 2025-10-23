<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DateOption>
 */
class DateOptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::inRandomOrder()->first()->id ?? Event::factory(),
            'proposed_date' => fake()->dateTimeBetween('+1 days', '+1 month')->format('Y-m-d'),
            'proposed_time' => fake()->time(),
        ];
    }
}

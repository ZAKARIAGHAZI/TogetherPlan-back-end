<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Participant>
 */
class ParticipantFactory extends Factory
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
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory(),
            'status' => fake()->randomElement(['invited', 'accepted', 'declined']),
        ];
    }
}

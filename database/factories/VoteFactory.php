<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\DateOption;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vote>
 */
class VoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $vote = $this->faker->randomElement(['yes', 'maybe', 'no']);

        $points = match ($vote) {
            'yes' => 2,
            'maybe' => 1,
            default => 0,
        };
        return [
            'user_id' => User::inRandomOrder()->first()->id ?: User::factory()->create()->id,
            'date_option_id' => DateOption::inRandomOrder()->first()->id ?: DateOption::factory()->create()->id,
            'vote' => $vote,
            'points' => $points,
        ];
    }
}

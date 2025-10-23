<?php

namespace Database\Seeders;

use App\Models\Vote;
use App\Models\Event;
use App\Models\DateOption;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create some users if none exist
        if (User::count() < 10) {
            User::factory(10)->create();
        }

        // Create events
        Event::factory(5)->create()->each(function ($event) {
            // Create 3 date options for each event
            DateOption::factory(3)->create(['event_id' => $event->id]);

            // If private, create 5 participants (random users)
            if ($event->privacy === 'private') {
                $users = User::inRandomOrder()->take(5)->pluck('id');
                foreach ($users as $userId) {
                    Participant::factory()->create([
                        'event_id' => $event->id,
                        'user_id' => $userId,
                        'status' => 'invited',
                    ]);
                }
            }
        });

        // Create votes (tie to existing participants & date options)
        $participants = Participant::all();
        $participants->each(function ($participant) {
            $event = $participant->event;
            $dateOption = $event->dateOptions()->inRandomOrder()->first();
            if ($dateOption) {
                Vote::factory()->create([
                    'user_id' => $participant->user_id,
                    'event_id' => $event->id,
                    'option_id' => $dateOption->id,
                    'vote' => ['yes', 'maybe', 'no'][rand(0, 2)],
                ]);
            }
        });
    }
}

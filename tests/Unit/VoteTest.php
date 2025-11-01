<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Event;
use App\Models\DateOption;
use App\Models\Vote;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_vote_and_points_are_assigned()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create();
        $dateOption = DateOption::factory()->create(['event_id' => $event->id]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/votes', [
                'date_option_id' => $dateOption->id,
                'vote' => 'yes'
            ])
            ->assertStatus(200)
            ->assertJson([
                'message' => 'Vote submitted successfully!'
            ]);

        $this->assertDatabaseHas('votes', [
            'user_id' => $user->id,
            'date_option_id' => $dateOption->id,
            'points' => 2
        ]);

        $event->refresh();
        $this->assertEquals($event->best_date_id, $dateOption->id);
    }

    public function test_user_cannot_vote_twice_for_same_date()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create();
        $dateOption = DateOption::factory()->create(['event_id' => $event->id]);

        Vote::create([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'date_option_id' => $dateOption->id,
            'vote' => 'maybe',
            'points' => 1
        ]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/votes', [
                'date_option_id' => $dateOption->id,
                'vote' => 'yes'
            ])
            ->assertStatus(400)
            ->assertJson([
                'message' => 'You have already voted for this date.'
            ]);
    }
}

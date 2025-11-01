<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Group;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_group()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/groups', [
            'name' => 'Groupe Test',
            'description' => 'Description Test',
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'Groupe Test']);

        $this->assertDatabaseHas('groups', ['name' => 'Groupe Test']);
    }

    public function test_user_can_invite_to_group()
    {
        $user = User::factory()->create();
        $invitee = User::factory()->create();
        $group = Group::factory()->create(['created_by' => $user->id]);
        $group->users()->attach($user->id);

        $response = $this->actingAs($user, 'sanctum')->postJson("/api/groups/{$group->id}/invite", [
            'emails' => [$invitee->email],
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Utilisateurs ajoutés et invitations envoyées !']);

        $this->assertDatabaseHas('group_user', [
            'user_id' => $invitee->id,
            'group_id' => $group->id,
        ]);
    }

    public function test_user_can_delete_group()
    {
        $user = User::factory()->create();
        $group = Group::factory()->create(['created_by' => $user->id]);
        $group->users()->attach($user->id);

        $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/groups/{$group->id}");
        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Groupe supprimé']);

        $this->assertDatabaseMissing('groups', ['id' => $group->id]);
    }
}

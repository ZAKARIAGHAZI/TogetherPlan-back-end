<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_users_can_register(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        // Vérifie que l'utilisateur est bien créé
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);

        // Vérifie que la réponse contient un token et les infos de l'utilisateur
        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'user' => ['id', 'name', 'email'],
                'token',
            ]);

        // Optionnel : vérifie que le token existe dans la table personal_access_tokens
        $userId = $response->json('user.id');
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $userId,
            'tokenable_type' => User::class,
        ]);
    }
}

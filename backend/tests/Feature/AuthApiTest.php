<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Boni Tester',
            'username' => 'boni_tester',
            'email' => 'boni@example.com',
            'password' => 'Qx!9vR2#TypingContest2026',
            'password_confirmation' => 'Qx!9vR2#TypingContest2026',
        ]);

        $response
            ->assertCreated()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token',
                    'token_type',
                    'expires_at',
                    'user' => ['id', 'name', 'email'],
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'boni@example.com',
        ]);
    }

    public function test_user_can_login_and_fetch_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'Boni Login',
            'username' => 'boni_login',
            'email' => 'login@example.com',
            'password' => 'Qx!9vR2#TypingContest2026',
        ]);

        $login = $this->postJson('/api/v1/auth/login', [
            'login' => 'login@example.com',
            'password' => 'Qx!9vR2#TypingContest2026',
        ]);

        $login
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token',
                    'token_type',
                    'expires_at',
                    'user' => ['id', 'name', 'email'],
                ],
            ]);

        $token = $login->json('data.token');

        $this->assertNotEmpty($token);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.id', $user->id);
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/auth/logout')
            ->assertOk()
            ->assertJsonPath('message', 'Logout successful');
    }
}

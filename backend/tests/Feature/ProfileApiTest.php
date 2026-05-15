<?php

namespace Tests\Feature;

use App\Models\Badge;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name'              => 'Test User',
            'username'          => 'testuser',
            'email'             => 'test@example.com',
            'email_verified_at' => now(),
            'country'           => 'US',
        ]);

        $this->token = $this->user->createToken('test-device')->plainTextToken;
    }

    private function authHeaders(): array
    {
        return ['Authorization' => 'Bearer ' . $this->token];
    }

    // ──────────────────────────────────────────────────────────────────────────────
    // GET /api/v1/profile
    // ──────────────────────────────────────────────────────────────────────────────

    public function test_authenticated_user_can_get_own_profile(): void
    {
        $response = $this->getJson('/api/v1/profile', $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.username', 'testuser')
            ->assertJsonPath('data.email', 'test@example.com')
            ->assertJsonStructure([
                'success', 'message',
                'data' => [
                    'id', 'username', 'name', 'email',
                    'avatar_url', 'bio', 'country',
                    'statistics', 'badges', 'privacy',
                ],
            ]);
    }

    // ──────────────────────────────────────────────────────────────────────────────
    // GET /api/v1/profile/{username} (public)
    // ──────────────────────────────────────────────────────────────────────────────

    public function test_public_profile_can_be_fetched_by_username(): void
    {
        $response = $this->getJson('/api/v1/profile/testuser');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.username', 'testuser');
    }

    public function test_public_profile_returns_404_for_unknown_user(): void
    {
        $response = $this->getJson('/api/v1/profile/nobody_here_xyz');

        $response->assertNotFound()
            ->assertJsonPath('success', false);
    }

    // ──────────────────────────────────────────────────────────────────────────────
    // PUT /api/v1/profile/update
    // ──────────────────────────────────────────────────────────────────────────────

    public function test_user_can_update_name_and_bio(): void
    {
        $response = $this->putJson('/api/v1/profile/update', [
            'name' => 'Updated Name',
            'bio'  => 'Hello world, I love typing!',
        ], $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Updated Name')
            ->assertJsonPath('data.bio', 'Hello world, I love typing!');
    }

    public function test_update_fails_with_duplicate_username(): void
    {
        User::factory()->create(['username' => 'takenuser', 'email_verified_at' => now()]);

        $response = $this->putJson('/api/v1/profile/update', [
            'username' => 'takenuser',
        ], $this->authHeaders());

        $response->assertUnprocessable();
    }

    public function test_update_validates_bio_max_length(): void
    {
        $response = $this->putJson('/api/v1/profile/update', [
            'bio' => str_repeat('a', 501),
        ], $this->authHeaders());

        $response->assertUnprocessable();
    }

    public function test_user_can_update_social_links(): void
    {
        $response = $this->putJson('/api/v1/profile/update', [
            'social_links' => [
                'github'  => 'https://github.com/testuser',
                'twitter' => 'https://twitter.com/testuser',
            ],
        ], $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('data.social_links.github', 'https://github.com/testuser');
    }

    // ──────────────────────────────────────────────────────────────────────────────
    // POST /api/v1/profile/avatar
    // ──────────────────────────────────────────────────────────────────────────────

    public function test_user_can_upload_avatar(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('avatar.jpg', 200, 200);

        $response = $this->postJson('/api/v1/profile/avatar', [
            'avatar' => $file,
        ], $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['avatar_url']]);
    }

    public function test_avatar_upload_rejects_non_image(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this->postJson('/api/v1/profile/avatar', [
            'avatar' => $file,
        ], $this->authHeaders());

        $response->assertUnprocessable();
    }

    public function test_avatar_upload_rejects_oversized_file(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('big.jpg')->size(3000); // 3 MB

        $response = $this->postJson('/api/v1/profile/avatar', [
            'avatar' => $file,
        ], $this->authHeaders());

        $response->assertUnprocessable();
    }

    // ──────────────────────────────────────────────────────────────────────────────
    // GET /api/v1/profile/stats
    // ──────────────────────────────────────────────────────────────────────────────

    public function test_user_can_get_statistics(): void
    {
        $response = $this->getJson('/api/v1/profile/stats', $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'avg_wpm', 'highest_wpm', 'avg_accuracy',
                    'total_matches', 'total_wins', 'win_rate',
                    'typing_streak', 'longest_streak', 'typing_hours',
                    'total_points',
                ],
            ]);
    }

    // ──────────────────────────────────────────────────────────────────────────────
    // GET /api/v1/profile/badges
    // ──────────────────────────────────────────────────────────────────────────────

    public function test_user_can_get_badges(): void
    {
        $response = $this->getJson('/api/v1/profile/badges', $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data']);
    }

    public function test_feature_badge_requires_ownership(): void
    {
        $badge = Badge::factory()->create();

        // User doesn't own this badge
        $response = $this->postJson('/api/v1/profile/badges/feature', [
            'badge_id' => $badge->id,
        ], $this->authHeaders());

        $response->assertForbidden();
    }

    // ──────────────────────────────────────────────────────────────────────────────
    // GET /api/v1/profile/matches
    // ──────────────────────────────────────────────────────────────────────────────

    public function test_user_can_get_match_history(): void
    {
        $response = $this->getJson('/api/v1/profile/matches', $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data', 'meta' => ['current_page', 'last_page', 'per_page', 'total']]);
    }

    // ──────────────────────────────────────────────────────────────────────────────
    // GET /api/v1/profile/activity
    // ──────────────────────────────────────────────────────────────────────────────

    public function test_user_can_get_activity_feed(): void
    {
        $response = $this->getJson('/api/v1/profile/activity', $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data', 'meta']);
    }

    // ──────────────────────────────────────────────────────────────────────────────
    // Unauthenticated requests
    // ──────────────────────────────────────────────────────────────────────────────

    public function test_profile_endpoints_require_auth(): void
    {
        $this->getJson('/api/v1/profile')->assertUnauthorized();
        $this->putJson('/api/v1/profile/update', [])->assertUnauthorized();
        $this->postJson('/api/v1/profile/avatar', [])->assertUnauthorized();
        $this->getJson('/api/v1/profile/stats')->assertUnauthorized();
        $this->getJson('/api/v1/profile/badges')->assertUnauthorized();
        $this->getJson('/api/v1/profile/matches')->assertUnauthorized();
        $this->getJson('/api/v1/profile/activity')->assertUnauthorized();
    }
}

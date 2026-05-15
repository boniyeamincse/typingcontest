<?php

namespace Tests\Feature;

use App\Events\Social\FriendRequestSent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class SocialRewardsAnalyticsApiTest extends TestCase
{
    use RefreshDatabase;

    private function authHeader(User $user): array
    {
        return ['Authorization' => 'Bearer '.$user->createToken('sra')->plainTextToken];
    }

    public function test_social_endpoints_require_auth(): void
    {
        $this->postJson('/api/v1/social/follow', [])->assertUnauthorized();
        $this->postJson('/api/v1/social/unfollow', [])->assertUnauthorized();
        $this->postJson('/api/v1/social/friend-request', [])->assertUnauthorized();
        $this->postJson('/api/v1/social/friend-request/respond', [])->assertUnauthorized();
        $this->getJson('/api/v1/social/feed')->assertUnauthorized();
    }

    public function test_user_can_follow_and_unfollow_another_user(): void
    {
        $follower = User::factory()->create(['email_verified_at' => now()]);
        $followed = User::factory()->create(['email_verified_at' => now()]);

        $headers = $this->authHeader($follower);

        $this->withHeaders($headers)
            ->postJson('/api/v1/social/follow', ['user_id' => $followed->id])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('user_follows', [
            'follower_id' => $follower->id,
            'followed_id' => $followed->id,
        ]);

        $this->withHeaders($headers)
            ->postJson('/api/v1/social/unfollow', ['user_id' => $followed->id])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('user_follows', [
            'follower_id' => $follower->id,
            'followed_id' => $followed->id,
        ]);
    }

    public function test_user_can_send_friend_request(): void
    {
        Event::fake([FriendRequestSent::class]);

        $sender = User::factory()->create(['email_verified_at' => now()]);
        $receiver = User::factory()->create(['email_verified_at' => now()]);

        $sendHeaders = $this->authHeader($sender);

        $this->withHeaders($sendHeaders)
            ->postJson('/api/v1/social/friend-request', ['user_id' => $receiver->id])
            ->assertOk()
            ->assertJsonPath('success', true);

        $requestId = (int) DB::table('friend_requests')
            ->where('sender_id', $sender->id)
            ->where('receiver_id', $receiver->id)
            ->value('id');

        $this->assertGreaterThan(0, $requestId);

        $this->assertDatabaseHas('friend_requests', [
            'id' => $requestId,
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'status' => 'pending',
        ]);

        Event::assertDispatched(FriendRequestSent::class);
    }

    public function test_user_can_accept_friend_request(): void
    {
        $sender = User::factory()->create(['email_verified_at' => now()]);
        $receiver = User::factory()->create(['email_verified_at' => now()]);

        $requestId = DB::table('friend_requests')->insertGetId([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->withHeaders($this->authHeader($receiver))
            ->postJson('/api/v1/social/friend-request/respond', [
                'request_id' => $requestId,
                'status' => 'accepted',
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'accepted');

        $this->assertDatabaseHas('friend_requests', [
            'id' => $requestId,
            'status' => 'accepted',
        ]);
    }

    public function test_user_can_fetch_social_feed(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        DB::table('social_feed_items')->insert([
            'user_id' => $user->id,
            'actor_user_id' => null,
            'type' => 'test_event',
            'title' => 'Feed item',
            'payload' => json_encode(['hello' => 'world']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->withHeaders($this->authHeader($user))
            ->getJson('/api/v1/social/feed?limit=10')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
            ]);
    }

    public function test_rewards_progress_returns_xp_and_computed_level(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'xp_points' => 900,
        ]);

        $this->withHeaders($this->authHeader($user))
            ->getJson('/api/v1/rewards/progress')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.xp', 900)
            ->assertJsonPath('data.level', 4);
    }

    public function test_rewards_streak_update_creates_or_updates_user_statistics(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->withHeaders($this->authHeader($user))
            ->postJson('/api/v1/rewards/streak/update')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.streak', 1);

        $this->assertDatabaseHas('user_statistics', [
            'user_id' => $user->id,
            'typing_streak' => 1,
            'longest_streak' => 1,
        ]);
    }

    public function test_analytics_daily_summary_returns_default_zero_values_without_snapshot(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->withHeaders($this->authHeader($user))
            ->getJson('/api/v1/analytics/daily-summary?date=2026-05-16')
            ->assertOk()
            ->assertJsonPath('snapshot_date', '2026-05-16')
            ->assertJsonPath('active_users', 0)
            ->assertJsonPath('matches_played', 0)
            ->assertJsonPath('avg_wpm', 0)
            ->assertJsonPath('revenue', 0);
    }

    public function test_analytics_daily_summary_returns_persisted_snapshot_values(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        DB::table('analytics_daily_snapshots')->insert([
            'snapshot_date' => '2026-05-16',
            'active_users' => 42,
            'matches_played' => 120,
            'avg_wpm' => 76.35,
            'revenue' => 199.50,
            'metadata' => json_encode(['source' => 'test']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->withHeaders($this->authHeader($user))
            ->getJson('/api/v1/analytics/daily-summary?date=2026-05-16')
            ->assertOk()
            ->assertJsonPath('snapshot_date', '2026-05-16')
            ->assertJsonPath('active_users', 42)
            ->assertJsonPath('matches_played', 120)
            ->assertJsonPath('avg_wpm', 76.35)
            ->assertJsonPath('revenue', 199.5)
            ->assertJsonPath('metadata.source', 'test');
    }
}

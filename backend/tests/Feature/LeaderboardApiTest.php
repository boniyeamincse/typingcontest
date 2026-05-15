<?php

namespace Tests\Feature;

use App\Models\Contest;
use App\Models\ContestRanking;
use App\Models\Leaderboard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaderboardApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_global_leaderboard_endpoint_returns_expected_json_contract(): void
    {
        $user = User::factory()->create([
            'username' => 'ranker1',
            'country' => 'US',
        ]);

        Leaderboard::query()->create([
            'user_id' => $user->id,
            'type' => 'global',
            'period_key' => 'all-time',
            'rank' => 1,
            'previous_rank' => 2,
            'rank_movement' => 1,
            'score' => 120.50,
            'wpm' => 110,
            'accuracy' => 98.20,
            'errors' => 1,
            'completion_time_ms' => 15000,
            'bonus_points' => 10,
            'medal' => 'gold',
        ]);

        $this->getJson('/api/v1/leaderboard/global')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Leaderboard fetched successfully')
            ->assertJsonPath('data.0.rank', 1)
            ->assertJsonPath('data.0.username', 'ranker1')
            ->assertJsonPath('data.0.country', 'US')
            ->assertJsonPath('data.0.rank_movement', 1)
            ->assertJsonPath('data.0.medal', 'gold');
    }

    public function test_country_leaderboard_uses_country_code_path_param(): void
    {
        $frUser = User::factory()->create(['username' => 'fr_user', 'country' => 'FR']);
        $usUser = User::factory()->create(['username' => 'us_user', 'country' => 'US']);

        Leaderboard::query()->create([
            'user_id' => $frUser->id,
            'type' => 'country',
            'period_key' => 'all-time',
            'country_code' => 'FR',
            'rank' => 1,
            'score' => 80,
            'wpm' => 80,
            'accuracy' => 97,
            'errors' => 2,
            'completion_time_ms' => 20000,
            'bonus_points' => 0,
        ]);

        Leaderboard::query()->create([
            'user_id' => $usUser->id,
            'type' => 'country',
            'period_key' => 'all-time',
            'country_code' => 'US',
            'rank' => 1,
            'score' => 90,
            'wpm' => 90,
            'accuracy' => 98,
            'errors' => 1,
            'completion_time_ms' => 18000,
            'bonus_points' => 0,
        ]);

        $this->getJson('/api/v1/leaderboard/country/FR')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.0.username', 'fr_user')
            ->assertJsonMissing(['username' => 'us_user']);
    }

    public function test_contest_leaderboard_endpoint_returns_rankings(): void
    {
        $contest = Contest::factory()->active()->create();
        $user = User::factory()->create(['username' => 'speedster', 'country' => 'ID']);

        ContestRanking::query()->create([
            'contest_id' => $contest->id,
            'user_id' => $user->id,
            'rank' => 1,
            'previous_rank' => 3,
            'rank_movement' => 2,
            'score' => 150.25,
            'wpm' => 125,
            'accuracy' => 99.10,
            'errors' => 0,
            'completion_time_ms' => 10000,
            'bonus_points' => 25,
            'medal' => 'gold',
        ]);

        $this->getJson('/api/v1/leaderboard/contest/' . $contest->id)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.0.rank', 1)
            ->assertJsonPath('data.0.username', 'speedster')
            ->assertJsonPath('data.0.medal', 'gold');
    }
}

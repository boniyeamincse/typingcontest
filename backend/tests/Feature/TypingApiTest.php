<?php

namespace Tests\Feature;

use App\Models\Contest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TypingApiTest extends TestCase
{
    use RefreshDatabase;

    private function authHeader(User $user): array
    {
        return ['Authorization' => 'Bearer ' . $user->createToken('typing')->plainTextToken];
    }

    public function test_user_can_start_typing_session(): void
    {
        $user = User::factory()->create();
        $contest = Contest::factory()->active()->create([
            'text_content' => 'hello world from typing contest module',
            'duration_seconds' => 60,
        ]);

        $this->withHeaders($this->authHeader($user))
            ->postJson('/api/v1/typing/start', [
                'contest_id' => $contest->id,
                'device_fingerprint' => 'dev-1',
            ])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Typing session started')
            ->assertJsonPath('data.contest_id', $contest->id);
    }

    public function test_duplicate_session_is_blocked(): void
    {
        $user = User::factory()->create();
        $contest = Contest::factory()->active()->create([
            'text_content' => 'hello world from typing contest module',
        ]);

        $headers = $this->authHeader($user);

        $this->withHeaders($headers)->postJson('/api/v1/typing/start', ['contest_id' => $contest->id])->assertCreated();

        $this->withHeaders($headers)
            ->postJson('/api/v1/typing/start', ['contest_id' => $contest->id])
            ->assertStatus(409)
            ->assertJsonPath('success', false);
    }

    public function test_user_can_update_and_submit_session(): void
    {
        $user = User::factory()->create();
        $contest = Contest::factory()->active()->create([
            'text_content' => 'hello world from typing contest module',
            'duration_seconds' => 90,
        ]);

        $headers = $this->authHeader($user);

        $start = $this->withHeaders($headers)->postJson('/api/v1/typing/start', ['contest_id' => $contest->id]);
        $start->assertCreated();

        $sessionId = (int) $start->json('data.id');

        $update = $this->withHeaders($headers)->postJson('/api/v1/typing/update', [
            'session_id' => $sessionId,
            'sequence' => 1,
            'typed_text' => 'hello world from typing contest module',
            'elapsed_ms' => 10000,
            'sync_interval_ms' => 300,
            'cursor_position' => 35,
        ]);

        $update->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.metrics.progress_percent', 100);

        $submit = $this->withHeaders($headers)->postJson('/api/v1/typing/submit', [
            'session_id' => $sessionId,
            'typed_text' => 'hello world from typing contest module',
            'elapsed_ms' => 12000,
        ]);

        $submit->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Typing session submitted')
            ->assertJsonPath('data.result.typing_session_id', $sessionId);
    }

    public function test_user_can_view_status_result_and_history(): void
    {
        $user = User::factory()->create();
        $contest = Contest::factory()->active()->create([
            'text_content' => 'hello world from typing contest module',
            'duration_seconds' => 90,
        ]);

        $headers = $this->authHeader($user);

        $start = $this->withHeaders($headers)->postJson('/api/v1/typing/start', ['contest_id' => $contest->id]);
        $sessionId = (int) $start->json('data.id');

        $this->withHeaders($headers)->postJson('/api/v1/typing/submit', [
            'session_id' => $sessionId,
            'typed_text' => 'hello world from typing contest module',
            'elapsed_ms' => 12000,
        ])->assertOk();

        $resultId = (int) \App\Models\TypingResult::query()->value('id');

        $this->withHeaders($headers)
            ->getJson("/api/v1/typing/status/{$sessionId}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->withHeaders($headers)
            ->getJson("/api/v1/typing/result/{$resultId}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->withHeaders($headers)
            ->getJson('/api/v1/typing/history')
            ->assertOk()
            ->assertJsonPath('success', true);
    }
}

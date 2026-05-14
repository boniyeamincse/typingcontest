<?php

namespace Tests\Feature;

use App\Models\Contest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContestApiTest extends TestCase
{
    use RefreshDatabase;

    private function authHeader(User $user): array
    {
        return ['Authorization' => 'Bearer '.$user->createToken('t')->plainTextToken];
    }

    public function test_guest_can_list_published_contests(): void
    {
        Contest::factory()->count(3)->create();
        Contest::factory()->state(['status' => 'draft'])->create();

        $this->getJson('/api/contests')
            ->assertOk()
            ->assertJsonPath('total', 3);
    }

    public function test_guest_can_view_published_contest(): void
    {
        $contest = Contest::factory()->create();

        $this->getJson("/api/contests/{$contest->id}")->assertOk();
    }

    public function test_guest_cannot_view_draft_contest(): void
    {
        $contest = Contest::factory()->state(['status' => 'draft'])->create();

        $this->getJson("/api/contests/{$contest->id}")->assertNotFound();
    }

    public function test_user_can_join_active_contest(): void
    {
        $user    = User::factory()->create();
        $contest = Contest::factory()->active()->create();

        $this->withHeaders($this->authHeader($user))
            ->postJson("/api/contests/{$contest->id}/join")
            ->assertOk()
            ->assertJsonPath('message', 'Joined successfully.');
    }

    public function test_user_cannot_join_completed_contest(): void
    {
        $user    = User::factory()->create();
        $contest = Contest::factory()->completed()->create();

        $this->withHeaders($this->authHeader($user))
            ->postJson("/api/contests/{$contest->id}/join")
            ->assertUnprocessable();
    }

    public function test_user_can_submit_result_and_see_rank(): void
    {
        $user    = User::factory()->create();
        $contest = Contest::factory()->active()->create();

        $headers = $this->authHeader($user);

        $this->withHeaders($headers)->postJson("/api/contests/{$contest->id}/join")->assertOk();

        $response = $this->withHeaders($headers)->postJson("/api/contests/{$contest->id}/submit", [
            'wpm'      => 80,
            'accuracy' => 95.5,
            'errors'   => 2,
        ]);

        $response->assertOk()
            ->assertJsonPath('result.wpm', 80)
            ->assertJsonPath('result.rank', 1);
    }

    public function test_admin_can_create_and_publish_contest(): void
    {
        $admin = User::factory()->create();

        $headers = $this->authHeader($admin);

        $create = $this->withHeaders($headers)->postJson('/api/admin/contests', [
            'title'            => 'Championship Round',
            'type'             => 'weekly',
            'text_content'     => str_repeat('A sentence for the typing contest. ', 5),
            'duration_seconds' => 60,
        ]);

        $create->assertCreated()->assertJsonPath('status', 'draft');

        $id = $create->json('id');

        $this->withHeaders($headers)
            ->postJson("/api/admin/contests/{$id}/publish")
            ->assertOk()
            ->assertJsonPath('contest.status', 'published');
    }
}

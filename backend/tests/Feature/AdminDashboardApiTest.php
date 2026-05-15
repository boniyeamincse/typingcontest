<?php

namespace Tests\Feature;

use App\Events\AdminNotificationBroadcasted;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Admin Dashboard API Feature Tests
 *
 * Covers role-matrix access, happy-path responses, and negative (forbidden) scenarios
 * for every admin dashboard module.
 */
class AdminDashboardApiTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ──────────────────────────────────────────────────────────────────

    private function authHeader(User $user): array
    {
        return ['Authorization' => 'Bearer ' . $user->createToken('test')->plainTextToken];
    }

    private function makeUser(string $role): User
    {
        Role::findOrCreate($role, 'api');
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole($role);

        return $user;
    }

    // ── 1. Dashboard Overview ─────────────────────────────────────────────────────

    /** @test */
    public function test_dashboard_overview_accessible_by_super_admin(): void
    {
        $user = $this->makeUser('super_admin');

        $this->withHeaders($this->authHeader($user))
            ->getJson('/api/v1/admin/dashboard/overview')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data']);
    }

    /** @test */
    public function test_dashboard_overview_accessible_by_contest_admin(): void
    {
        $user = $this->makeUser('contest_admin');

        $this->withHeaders($this->authHeader($user))
            ->getJson('/api/v1/admin/dashboard/overview')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    /** @test */
    public function test_dashboard_overview_accessible_by_legacy_admin(): void
    {
        $user = $this->makeUser('admin');

        $this->withHeaders($this->authHeader($user))
            ->getJson('/api/v1/admin/dashboard/overview')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    /** @test */
    public function test_dashboard_overview_rejects_unauthenticated(): void
    {
        $this->getJson('/api/v1/admin/dashboard/overview')
            ->assertUnauthorized();
    }

    /** @test */
    public function test_dashboard_overview_rejects_regular_user(): void
    {
        $user = $this->makeUser('free_user');

        $this->withHeaders($this->authHeader($user))
            ->getJson('/api/v1/admin/dashboard/overview')
            ->assertForbidden();
    }

    // ── 2. User Management ────────────────────────────────────────────────────────

    /** @test */
    public function test_user_moderator_can_list_users(): void
    {
        $admin = $this->makeUser('user_moderator');

        $this->withHeaders($this->authHeader($admin))
            ->getJson('/api/v1/admin/users')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    /** @test */
    public function test_user_moderator_can_ban_user(): void
    {
        $admin = $this->makeUser('user_moderator');
        $target = User::factory()->create(['email_verified_at' => now()]);

        $this->withHeaders($this->authHeader($admin))
            ->postJson('/api/v1/admin/users/ban', [
                'user_id' => $target->id,
                'reason' => 'Cheating violation',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'is_banned' => true,
        ]);
    }

    /** @test */
    public function test_user_moderator_can_unban_user(): void
    {
        $admin = $this->makeUser('user_moderator');
        $target = User::factory()->create([
            'email_verified_at' => now(),
            'is_banned' => true,
            'banned_reason' => 'spam',
        ]);

        $this->withHeaders($this->authHeader($admin))
            ->postJson('/api/v1/admin/users/unban', [
                'user_id' => $target->id,
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'is_banned' => false,
        ]);
    }

    /** @test */
    public function test_user_moderator_can_suspend_user(): void
    {
        $admin = $this->makeUser('user_moderator');
        $target = User::factory()->create(['email_verified_at' => now()]);

        $this->withHeaders($this->authHeader($admin))
            ->postJson('/api/v1/admin/users/suspend', [
                'user_id' => $target->id,
                'until' => now()->addDays(7)->toDateString(),
                'reason' => 'Repeated offence',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    /** @test */
    public function test_user_moderator_can_reset_user_password(): void
    {
        $admin = $this->makeUser('user_moderator');
        $target = User::factory()->create(['email_verified_at' => now()]);

        $this->withHeaders($this->authHeader($admin))
            ->postJson('/api/v1/admin/users/reset-password', [
                'user_id' => $target->id,
                'new_password' => 'NewPassword@99',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    /** @test */
    public function test_user_moderator_can_list_suspicious_users(): void
    {
        $admin = $this->makeUser('user_moderator');

        $this->withHeaders($this->authHeader($admin))
            ->getJson('/api/v1/admin/users/suspicious')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    /** @test */
    public function test_contest_admin_cannot_access_users_module(): void
    {
        $admin = $this->makeUser('contest_admin');

        $this->withHeaders($this->authHeader($admin))
            ->getJson('/api/v1/admin/users')
            ->assertForbidden();
    }

    /** @test */
    public function test_support_admin_cannot_access_users_module(): void
    {
        $admin = $this->makeUser('support_admin');

        $this->withHeaders($this->authHeader($admin))
            ->getJson('/api/v1/admin/users')
            ->assertForbidden();
    }

    // ── 3. Contest Management ─────────────────────────────────────────────────────

    /** @test */
    public function test_contest_admin_can_list_contests(): void
    {
        $admin = $this->makeUser('contest_admin');

        $this->withHeaders($this->authHeader($admin))
            ->getJson('/api/v1/admin/contests')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    /** @test */
    public function test_user_moderator_cannot_access_contests_module(): void
    {
        $admin = $this->makeUser('user_moderator');

        $this->withHeaders($this->authHeader($admin))
            ->getJson('/api/v1/admin/contests')
            ->assertForbidden();
    }

    /** @test */
    public function test_content_manager_cannot_access_contests_module(): void
    {
        $admin = $this->makeUser('content_manager');

        $this->withHeaders($this->authHeader($admin))
            ->getJson('/api/v1/admin/contests')
            ->assertForbidden();
    }

    // ── 4. Content / Paragraphs ────────────────────────────────────────────────────

    /** @test */
    public function test_content_manager_can_list_paragraphs(): void
    {
        $admin = $this->makeUser('content_manager');

        $this->withHeaders($this->authHeader($admin))
            ->getJson('/api/v1/admin/content/paragraphs')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    /** @test */
    public function test_content_manager_can_create_paragraph(): void
    {
        $admin = $this->makeUser('content_manager');

        $this->withHeaders($this->authHeader($admin))
            ->postJson('/api/v1/admin/content/paragraphs', [
                'content' => 'The quick brown fox jumps over the lazy dog and runs away.',
                'difficulty' => 'easy',
                'language' => 'english',
            ])
            ->assertStatus(201)
            ->assertJsonPath('success', true);
    }

    /** @test */
    public function test_user_moderator_cannot_manage_content(): void
    {
        $admin = $this->makeUser('user_moderator');

        $this->withHeaders($this->authHeader($admin))
            ->getJson('/api/v1/admin/content/paragraphs')
            ->assertForbidden();
    }

    // ── 5. Subscriptions ──────────────────────────────────────────────────────────

    /** @test */
    public function test_contest_admin_can_list_subscriptions(): void
    {
        $admin = $this->makeUser('contest_admin');

        $this->withHeaders($this->authHeader($admin))
            ->getJson('/api/v1/admin/subscriptions')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    /** @test */
    public function test_contest_admin_can_list_plans(): void
    {
        $admin = $this->makeUser('contest_admin');

        $this->withHeaders($this->authHeader($admin))
            ->getJson('/api/v1/admin/subscriptions/plans')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    /** @test */
    public function test_support_admin_cannot_access_subscriptions_module(): void
    {
        $admin = $this->makeUser('support_admin');

        $this->withHeaders($this->authHeader($admin))
            ->getJson('/api/v1/admin/subscriptions')
            ->assertForbidden();
    }

    // ── 6. Payments ───────────────────────────────────────────────────────────────

    /** @test */
    public function test_contest_admin_can_list_payments(): void
    {
        $admin = $this->makeUser('contest_admin');

        $this->withHeaders($this->authHeader($admin))
            ->getJson('/api/v1/admin/payments')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    /** @test */
    public function test_user_moderator_cannot_access_payments_module(): void
    {
        $admin = $this->makeUser('user_moderator');

        $this->withHeaders($this->authHeader($admin))
            ->getJson('/api/v1/admin/payments')
            ->assertForbidden();
    }

    // ── 7. Badges ─────────────────────────────────────────────────────────────────

    /** @test */
    public function test_content_manager_can_list_badges(): void
    {
        $admin = $this->makeUser('content_manager');

        $this->withHeaders($this->authHeader($admin))
            ->getJson('/api/v1/admin/badges')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    /** @test */
    public function test_content_manager_can_create_badge(): void
    {
        $admin = $this->makeUser('content_manager');

        $this->withHeaders($this->authHeader($admin))
            ->postJson('/api/v1/admin/badges', [
                'name' => 'Speed Demon',
                'slug' => 'speed-demon',
                'description' => 'Awarded for 100 WPM',
                'requirement_type' => 'wpm',
                'requirement_value' => 100,
            ])
            ->assertStatus(201)
            ->assertJsonPath('success', true);
    }

    /** @test */
    public function test_contest_admin_cannot_manage_badges(): void
    {
        // contest_admin is not in the 'content' module map
        // super_admin bypasses, admin is allowed; contest_admin should be forbidden
        $admin = $this->makeUser('contest_admin');

        $this->withHeaders($this->authHeader($admin))
            ->getJson('/api/v1/admin/badges')
            ->assertForbidden();
    }

    // ── 8. Leaderboard ────────────────────────────────────────────────────────────

    /** @test */
    public function test_contest_admin_can_export_leaderboard(): void
    {
        $admin = $this->makeUser('contest_admin');

        $this->withHeaders($this->authHeader($admin))
            ->getJson('/api/v1/admin/leaderboard/export')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    /** @test */
    public function test_contest_admin_can_recalculate_leaderboard(): void
    {
        $admin = $this->makeUser('contest_admin');

        $this->withHeaders($this->authHeader($admin))
            ->postJson('/api/v1/admin/leaderboard/recalculate', [])
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    /** @test */
    public function test_content_manager_cannot_access_leaderboard_module(): void
    {
        $admin = $this->makeUser('content_manager');

        $this->withHeaders($this->authHeader($admin))
            ->getJson('/api/v1/admin/leaderboard/export')
            ->assertForbidden();
    }

    // ── 9. Reports ────────────────────────────────────────────────────────────────

    /** @test */
    public function test_contest_admin_can_list_reports(): void
    {
        $admin = $this->makeUser('contest_admin');

        $this->withHeaders($this->authHeader($admin))
            ->getJson('/api/v1/admin/reports')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    /** @test */
    public function test_support_admin_can_list_reports(): void
    {
        $admin = $this->makeUser('support_admin');

        $this->withHeaders($this->authHeader($admin))
            ->getJson('/api/v1/admin/reports')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    /** @test */
    public function test_contest_admin_can_queue_report(): void
    {
        $admin = $this->makeUser('contest_admin');

        $this->withHeaders($this->authHeader($admin))
            ->postJson('/api/v1/admin/reports/queue', [
                'report_type' => 'subscriptions_summary',
                'date_from' => now()->subDays(30)->toDateString(),
                'date_to' => now()->toDateString(),
            ])
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    /** @test */
    public function test_content_manager_cannot_access_reports_module(): void
    {
        $admin = $this->makeUser('content_manager');

        $this->withHeaders($this->authHeader($admin))
            ->getJson('/api/v1/admin/reports')
            ->assertForbidden();
    }

    // ── 10. Security ──────────────────────────────────────────────────────────────

    /** @test */
    public function test_user_moderator_can_view_cheating_users(): void
    {
        $admin = $this->makeUser('user_moderator');

        $this->withHeaders($this->authHeader($admin))
            ->getJson('/api/v1/admin/security/cheating-users')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    /** @test */
    public function test_user_moderator_can_view_suspicious_logins(): void
    {
        $admin = $this->makeUser('user_moderator');

        $this->withHeaders($this->authHeader($admin))
            ->getJson('/api/v1/admin/security/suspicious-logins')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    /** @test */
    public function test_user_moderator_can_block_ip(): void
    {
        $admin = $this->makeUser('user_moderator');

        $this->withHeaders($this->authHeader($admin))
            ->postJson('/api/v1/admin/security/blocks', [
                'type' => 'ip',
                'value' => '192.168.1.100',
                'reason' => 'Brute force attempt',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    /** @test */
    public function test_contest_admin_cannot_access_security_module(): void
    {
        $admin = $this->makeUser('contest_admin');

        $this->withHeaders($this->authHeader($admin))
            ->getJson('/api/v1/admin/security/cheating-users')
            ->assertForbidden();
    }

    // ── 11. Support Tickets ───────────────────────────────────────────────────────

    /** @test */
    public function test_support_admin_can_list_tickets(): void
    {
        $admin = $this->makeUser('support_admin');

        $this->withHeaders($this->authHeader($admin))
            ->getJson('/api/v1/admin/support/tickets')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    /** @test */
    public function test_support_admin_can_create_ticket(): void
    {
        $admin = $this->makeUser('support_admin');
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->withHeaders($this->authHeader($admin))
            ->postJson('/api/v1/admin/support/tickets', [
                'user_id' => $user->id,
                'subject' => 'Account billing issue',
                'description' => 'User reports duplicate charge.',
                'priority' => 'high',
            ])
            ->assertStatus(201)
            ->assertJsonPath('success', true);
    }

    /** @test */
    public function test_support_admin_can_close_ticket(): void
    {
        $admin = $this->makeUser('support_admin');
        $user = User::factory()->create(['email_verified_at' => now()]);

        // Create a ticket first
        $createResponse = $this->withHeaders($this->authHeader($admin))
            ->postJson('/api/v1/admin/support/tickets', [
                'user_id' => $user->id,
                'subject' => 'Test ticket',
                'description' => 'Close me.',
            ]);

        $createResponse->assertStatus(201);
        $ticketId = $createResponse->json('data.id');

        $this->withHeaders($this->authHeader($admin))
            ->postJson("/api/v1/admin/support/tickets/{$ticketId}/close")
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    /** @test */
    public function test_contest_admin_cannot_access_support_module(): void
    {
        $admin = $this->makeUser('contest_admin');

        $this->withHeaders($this->authHeader($admin))
            ->getJson('/api/v1/admin/support/tickets')
            ->assertForbidden();
    }

    // ── 12. CMS ───────────────────────────────────────────────────────────────────

    /** @test */
    public function test_content_manager_can_list_cms_pages(): void
    {
        $admin = $this->makeUser('content_manager');

        $this->withHeaders($this->authHeader($admin))
            ->getJson('/api/v1/admin/cms/pages')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    /** @test */
    public function test_content_manager_can_list_cms_banners(): void
    {
        $admin = $this->makeUser('content_manager');

        $this->withHeaders($this->authHeader($admin))
            ->getJson('/api/v1/admin/cms/banners')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    /** @test */
    public function test_content_manager_can_save_cms_page(): void
    {
        $admin = $this->makeUser('content_manager');

        $this->withHeaders($this->authHeader($admin))
            ->postJson('/api/v1/admin/cms/pages', [
                'slug' => 'terms-of-service',
                'title' => 'Terms of Service',
                'body' => 'By using this service you agree...',
                'is_published' => true,
            ])
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    /** @test */
    public function test_support_admin_cannot_access_cms_module(): void
    {
        $admin = $this->makeUser('support_admin');

        $this->withHeaders($this->authHeader($admin))
            ->getJson('/api/v1/admin/cms/pages')
            ->assertForbidden();
    }

    // ── 13. Notifications ────────────────────────────────────────────────────────

    /** @test */
    public function test_support_admin_can_send_notification(): void
    {
        Event::fake([AdminNotificationBroadcasted::class]);
        $admin = $this->makeUser('support_admin');

        $this->withHeaders($this->authHeader($admin))
            ->postJson('/api/v1/admin/notifications/send', [
                'title' => 'System maintenance',
                'message' => 'The platform will be down for 30 minutes tonight.',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        Event::assertDispatched(AdminNotificationBroadcasted::class);
    }

    /** @test */
    public function test_contest_admin_can_send_notification(): void
    {
        Event::fake([AdminNotificationBroadcasted::class]);
        $admin = $this->makeUser('contest_admin');

        $this->withHeaders($this->authHeader($admin))
            ->postJson('/api/v1/admin/notifications/send', [
                'title' => 'New contest live',
                'message' => 'A new contest has started.',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        Event::assertDispatched(AdminNotificationBroadcasted::class);
    }

    /** @test */
    public function test_content_manager_cannot_send_notifications(): void
    {
        Event::fake([AdminNotificationBroadcasted::class]);
        $admin = $this->makeUser('content_manager');

        $this->withHeaders($this->authHeader($admin))
            ->postJson('/api/v1/admin/notifications/send', [
                'title' => 'Test',
                'message' => 'Body',
            ])
            ->assertForbidden();

        Event::assertNotDispatched(AdminNotificationBroadcasted::class);
    }

    // ── 14. System Monitoring ─────────────────────────────────────────────────────

    /** @test */
    public function test_contest_admin_can_view_system_monitoring(): void
    {
        $admin = $this->makeUser('contest_admin');

        $this->withHeaders($this->authHeader($admin))
            ->getJson('/api/v1/admin/system/monitoring')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    /** @test */
    public function test_contest_admin_can_view_activity_logs(): void
    {
        $admin = $this->makeUser('contest_admin');

        $this->withHeaders($this->authHeader($admin))
            ->getJson('/api/v1/admin/activity-logs')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    /** @test */
    public function test_support_admin_cannot_access_system_module(): void
    {
        $admin = $this->makeUser('support_admin');

        $this->withHeaders($this->authHeader($admin))
            ->getJson('/api/v1/admin/system/monitoring')
            ->assertForbidden();
    }

    // ── 15. Role Assignment ───────────────────────────────────────────────────────

    /** @test */
    public function test_super_admin_can_list_roles(): void
    {
        $admin = $this->makeUser('super_admin');

        $this->withHeaders($this->authHeader($admin))
            ->getJson('/api/v1/admin/roles')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    /** @test */
    public function test_super_admin_can_assign_role(): void
    {
        $admin = $this->makeUser('super_admin');
        $target = User::factory()->create(['email_verified_at' => now()]);
        Role::findOrCreate('contest_admin', 'api');

        $this->withHeaders($this->authHeader($admin))
            ->postJson('/api/v1/admin/roles/assign', [
                'user_id' => $target->id,
                'roles' => ['contest_admin'],
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertTrue($target->fresh()->hasRole('contest_admin'));
    }

    /** @test */
    public function test_contest_admin_cannot_access_roles_module(): void
    {
        $admin = $this->makeUser('contest_admin');

        $this->withHeaders($this->authHeader($admin))
            ->getJson('/api/v1/admin/roles')
            ->assertForbidden();
    }

    /** @test */
    public function test_admin_legacy_role_cannot_access_roles_module(): void
    {
        // Legacy 'admin' role is not in the roles module map (super_admin only)
        $admin = $this->makeUser('admin');

        $this->withHeaders($this->authHeader($admin))
            ->getJson('/api/v1/admin/roles')
            ->assertForbidden();
    }

    // ── 16. Super Admin Bypass ────────────────────────────────────────────────────

    /** @test */
    public function test_super_admin_bypasses_all_module_restrictions(): void
    {
        $admin = $this->makeUser('super_admin');
        $headers = $this->authHeader($admin);

        $endpoints = [
            ['GET', '/api/v1/admin/users'],
            ['GET', '/api/v1/admin/contests'],
            ['GET', '/api/v1/admin/content/paragraphs'],
            ['GET', '/api/v1/admin/subscriptions'],
            ['GET', '/api/v1/admin/payments'],
            ['GET', '/api/v1/admin/badges'],
            ['GET', '/api/v1/admin/leaderboard/export'],
            ['GET', '/api/v1/admin/reports'],
            ['GET', '/api/v1/admin/security/cheating-users'],
            ['GET', '/api/v1/admin/support/tickets'],
            ['GET', '/api/v1/admin/cms/pages'],
            ['GET', '/api/v1/admin/system/monitoring'],
            ['GET', '/api/v1/admin/roles'],
        ];

        foreach ($endpoints as [$method, $uri]) {
            $response = $this->withHeaders($headers)->json($method, $uri);
            $this->assertNotEquals(403, $response->status(), "super_admin was forbidden at {$method} {$uri}");
        }
    }

    // ── 17. Admin Logout ──────────────────────────────────────────────────────────

    /** @test */
    public function test_admin_can_logout(): void
    {
        $admin = $this->makeUser('admin');

        $this->withHeaders($this->authHeader($admin))
            ->postJson('/api/v1/admin/logout')
            ->assertOk()
            ->assertJsonPath('success', true);
    }
}

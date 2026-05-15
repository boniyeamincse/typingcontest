<?php

namespace Tests\Feature;

use App\Models\CouponCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminSubscriptionPaymentApiTest extends TestCase
{
    use RefreshDatabase;

    private function authHeader(User $user): array
    {
        return ['Authorization' => 'Bearer ' . $user->createToken('admin')->plainTextToken];
    }

    private function adminUser(): User
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->markEmailAsVerified();
        Role::findOrCreate('admin', 'api');
        $admin->assignRole('admin');

        return $admin;
    }

    public function test_admin_can_create_update_toggle_coupon(): void
    {
        $admin = $this->adminUser();
        $headers = $this->authHeader($admin);

        $create = $this->withHeaders($headers)->postJson('/api/v1/admin/coupons', [
            'code' => 'SUMMER25',
            'discount_type' => 'percentage',
            'discount_value' => 25,
            'usage_limit' => 100,
            'per_user_limit' => 2,
        ]);

        $create->assertCreated()->assertJsonPath('success', true);
        $id = (int) $create->json('data.id');

        $this->withHeaders($headers)
            ->putJson('/api/v1/admin/coupons/' . $id, [
                'discount_value' => 30,
            ])
            ->assertOk()
            ->assertJsonPath('data.discount_value', '30.00');

        $this->withHeaders($headers)
            ->postJson('/api/v1/admin/coupons/' . $id . '/toggle')
            ->assertOk()
            ->assertJsonPath('data.is_active', false);
    }

    public function test_admin_can_apply_manual_subscription_override(): void
    {
        $admin = $this->adminUser();
        $target = User::factory()->create(['email_verified_at' => now(), 'plan_type' => 'free']);
        $target->markEmailAsVerified();

        $this->withHeaders($this->authHeader($admin))
            ->postJson('/api/v1/admin/subscriptions/override', [
                'user_id' => $target->id,
                'plan_code' => 'vip_monthly',
                'status' => 'active',
                'reason' => 'manual grant',
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.plan.code', 'vip_monthly');

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'plan_type' => 'vip',
        ]);
    }

    public function test_admin_can_refund_paid_payment_and_revert_user_to_free(): void
    {
        $user = User::factory()->create(['email_verified_at' => now(), 'plan_type' => 'free']);
        $user->markEmailAsVerified();
        $userHeaders = $this->authHeader($user);

        $subscribe = $this->withHeaders($userHeaders)->postJson('/api/v1/subscription/subscribe', [
            'plan_code' => 'pro_monthly',
            'gateway' => 'bkash',
        ]);

        $subscribe->assertOk();
        $paymentIntent = (string) $subscribe->json('data.payment.payment_intent_id');

        $this->withHeaders($userHeaders)->postJson('/api/v1/payment/verify', [
            'payment_intent_id' => $paymentIntent,
            'status' => 'success',
            'amount' => 199,
            'gateway_reference' => 'BKASH-ADMIN-REF',
            'external_transaction_id' => 'BKASH-ADMIN-TXN-1',
        ])->assertOk();

        Role::findOrCreate('admin', 'api');
        $user->assignRole('admin');

        $this->withHeaders($this->authHeader($user))
            ->postJson('/api/v1/admin/payments/' . $paymentIntent . '/refund', [
                'reason' => 'customer request',
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.payment.status', 'refunded');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'plan_type' => 'free',
        ]);

        $this->assertDatabaseHas('payments', [
            'payment_intent_id' => $paymentIntent,
            'status' => 'refunded',
        ]);
    }

    public function test_non_admin_cannot_access_admin_subscription_payment_endpoints(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->markEmailAsVerified();
        $headers = $this->authHeader($user);

        $this->withHeaders($headers)
            ->getJson('/api/v1/admin/coupons')
            ->assertStatus(403);

        $this->withHeaders($headers)
            ->postJson('/api/v1/admin/subscriptions/override', [
                'user_id' => $user->id,
                'plan_code' => 'pro_monthly',
            ])
            ->assertStatus(403);
    }

    public function test_admin_can_get_subscription_payment_report_with_filters(): void
    {
        $admin = $this->adminUser();

        $coupon = CouponCode::query()->create([
            'code' => 'REPORT10',
            'discount_type' => 'fixed',
            'discount_value' => 10,
            'per_user_limit' => 1,
            'is_active' => true,
        ]);

        $paidHeaders = $this->authHeader($admin);

        $subscribe = $this->withHeaders($paidHeaders)->postJson('/api/v1/subscription/subscribe', [
            'plan_code' => 'pro_monthly',
            'gateway' => 'bkash',
            'coupon_code' => 'REPORT10',
        ]);

        $intent = (string) $subscribe->json('data.payment.payment_intent_id');

        $this->withHeaders($paidHeaders)->postJson('/api/v1/payment/verify', [
            'payment_intent_id' => $intent,
            'status' => 'success',
            'amount' => 189,
            'gateway_reference' => 'BKASH-REPORT-REF',
            'external_transaction_id' => 'BKASH-REPORT-TXN',
        ])->assertOk();

        Role::findOrCreate('admin', 'api');
        $admin->syncRoles(['admin']);

        $this->withHeaders($this->authHeader($admin->fresh()))
            ->getJson('/api/v1/admin/reports/subscriptions-payments?gateway=bkash&payment_status=paid&coupon_code=REPORT10&limit=10')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.filters.gateway', 'bkash')
            ->assertJsonPath('data.filters.payment_status', 'paid')
            ->assertJsonPath('data.filters.coupon_code', 'REPORT10')
            ->assertJsonPath('data.summary.total_transactions', 1)
            ->assertJsonPath('data.summary.paid_amount', 189)
            ->assertJsonPath('data.rows.0.payment_intent_id', $intent)
            ->assertJsonPath('data.rows.0.gateway', 'bkash');

        $this->withHeaders($this->authHeader($admin->fresh()))
            ->getJson('/api/v1/admin/reports/subscriptions-payments?refund_reason=customer&limit=10')
            ->assertOk()
            ->assertJsonPath('data.summary.total_transactions', 0);

        $this->withHeaders($this->authHeader($admin->fresh()))
            ->postJson('/api/v1/admin/payments/' . $intent . '/refund', ['reason' => 'customer requested'])
            ->assertOk();

        $this->withHeaders($this->authHeader($admin->fresh()))
            ->getJson('/api/v1/admin/reports/subscriptions-payments?payment_status=refunded&refund_reason=customer&limit=10')
            ->assertOk()
            ->assertJsonPath('data.summary.total_transactions', 1)
            ->assertJsonPath('data.summary.refunded_amount', 189)
            ->assertJsonPath('data.rows.0.status', 'refunded');
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SubscriptionPaymentApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    private function authHeader(User $user): array
    {
        return ['Authorization' => 'Bearer ' . $user->createToken('subscription')->plainTextToken];
    }

    public function test_plans_endpoint_returns_seeded_plans(): void
    {
        $this->getJson('/api/v1/plans')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.0.code', 'free_monthly');
    }

    public function test_user_can_subscribe_and_verify_payment_for_pro_plan(): void
    {
        $user = User::factory()->create([
            'plan_type' => 'free',
            'email_verified_at' => now(),
        ]);
        $headers = $this->authHeader($user);

        $subscribe = $this->withHeaders($headers)->postJson('/api/v1/subscription/subscribe', [
            'plan_code' => 'pro_monthly',
            'gateway' => 'bkash',
        ]);

        $subscribe->assertOk()->assertJsonPath('success', true);
        $paymentIntent = (string) $subscribe->json('data.payment.payment_intent_id');

        $create = $this->withHeaders($headers)->postJson('/api/v1/payment/create', [
            'payment_intent_id' => $paymentIntent,
        ]);

        $create->assertOk()->assertJsonPath('success', true);

        $verify = $this->withHeaders($headers)->postJson('/api/v1/payment/verify', [
            'payment_intent_id' => $paymentIntent,
            'status' => 'success',
            'amount' => 199,
            'gateway_reference' => 'BKASH-REF-1',
            'external_transaction_id' => 'BKASH-TXN-1',
        ]);

        $verify->assertOk()->assertJsonPath('success', true);

        $current = $this->withHeaders($headers)->getJson('/api/v1/subscription/current');

        $current->assertOk()->assertJsonPath('data.tier', 'pro');
    }

    public function test_feature_route_blocks_free_user_and_allows_pro_user(): void
    {
        $freeUser = User::factory()->create(['email_verified_at' => now()]);
        $freeHeaders = $this->authHeader($freeUser);

        $this->withHeaders($freeHeaders)
            ->getJson('/api/v1/features/analytics')
            ->assertStatus(403)
            ->assertJsonPath('success', false);

        $proUser = User::factory()->create(['email_verified_at' => now()]);
        $proHeaders = $this->authHeader($proUser);

        $subscribe = $this->withHeaders($proHeaders)->postJson('/api/v1/subscription/subscribe', [
            'plan_code' => 'pro_monthly',
            'gateway' => 'bkash',
        ]);

        $subscribe->assertOk();

        $paymentIntent = (string) $subscribe->json('data.payment.payment_intent_id');

        $this->withHeaders($proHeaders)->postJson('/api/v1/payment/verify', [
            'payment_intent_id' => $paymentIntent,
            'status' => 'success',
            'amount' => 199,
            'gateway_reference' => 'BKASH-REF-2',
            'external_transaction_id' => 'BKASH-TXN-2',
        ])->assertOk();

        $this->withHeaders($proHeaders)
            ->getJson('/api/v1/subscription/current')
            ->assertOk()
            ->assertJsonPath('data.tier', 'pro');

        $this->withHeaders($proHeaders)
            ->getJson('/api/v1/features/analytics')
            ->assertOk()
            ->assertJsonPath('success', true);
    }
}

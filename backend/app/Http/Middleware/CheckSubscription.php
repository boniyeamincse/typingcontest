<?php

namespace App\Http\Middleware;

use App\Services\Subscription\SubscriptionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    public function __construct(private readonly SubscriptionService $subscriptionService) {}

    public function handle(Request $request, Closure $next, ?string $required = 'pro'): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $current = $this->subscriptionService->getCurrent($user);
        $tier = $current['tier'] ?? 'free';

        if (!$this->isAllowed($tier, (string) $required)) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription upgrade required',
            ], 403);
        }

        return $next($request);
    }

    private function isAllowed(string $tier, string $required): bool
    {
        $levels = ['free' => 1, 'pro' => 2, 'vip' => 3];
        $requiredTier = $this->normalizeRequired($required);

        return ($levels[$tier] ?? 1) >= ($levels[$requiredTier] ?? 2);
    }

    private function normalizeRequired(string $required): string
    {
        return match ($required) {
            'analytics', 'multiplayer', 'premium_leaderboard', 'contest', 'pro' => 'pro',
            'ai', 'vip_leaderboard', 'vip' => 'vip',
            default => 'pro',
        };
    }
}

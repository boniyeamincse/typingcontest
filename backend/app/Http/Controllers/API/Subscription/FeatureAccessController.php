<?php

namespace App\Http\Controllers\API\Subscription;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class FeatureAccessController extends Controller
{
    public function contests(): JsonResponse
    {
        return $this->ok('Premium contests access granted');
    }

    public function analytics(): JsonResponse
    {
        return $this->ok('Advanced analytics access granted');
    }

    public function multiplayer(): JsonResponse
    {
        return $this->ok('Multiplayer mode access granted');
    }

    public function aiCoach(): JsonResponse
    {
        return $this->ok('AI coach access granted');
    }

    public function premiumLeaderboard(): JsonResponse
    {
        return $this->ok('Premium leaderboard access granted');
    }

    private function ok(string $message): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => ['authorized' => true],
        ]);
    }
}

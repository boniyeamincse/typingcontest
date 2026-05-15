<?php

namespace App\Http\Controllers\API\Rewards;

use App\Http\Controllers\Controller;
use App\Services\Rewards\StreakService;
use App\Services\Rewards\XpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RewardsController extends Controller
{
    public function __construct(
        private readonly XpService $xpService,
        private readonly StreakService $streakService,
    ) {
    }

    public function progress(Request $request): JsonResponse
    {
        $user = $request->user();
        $xp = (int) ($user?->xp_points ?? 0);

        return response()->json([
            'success' => true,
            'message' => 'Rewards progress fetched successfully',
            'data' => [
                'xp' => $xp,
                'level' => $this->xpService->toLevel($xp),
            ],
        ]);
    }

    public function updateStreak(Request $request): JsonResponse
    {
        $result = $this->streakService->updateAfterActivity((int) $request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Streak updated successfully',
            'data' => $result,
        ]);
    }
}

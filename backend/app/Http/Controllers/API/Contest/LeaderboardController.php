<?php

namespace App\Http\Controllers\API\Contest;

use App\Http\Controllers\Controller;
use App\Http\Resources\Contest\LeaderboardResource;
use App\Models\Contest;
use App\Services\Contest\LeaderboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaderboardController extends Controller
{
    public function __construct(private readonly LeaderboardService $leaderboardService) {}

    public function contest(Request $request, Contest $contest): JsonResponse
    {
        $limit = min((int) $request->query('limit', 50), 200);
        $rows  = $this->leaderboardService->getContestLeaderboard($contest, $limit);

        return response()->json([
            'success' => true,
            'message' => 'Contest leaderboard retrieved successfully.',
            'data'    => LeaderboardResource::collection($rows),
        ]);
    }

    public function global(Request $request): JsonResponse
    {
        $limit = min((int) $request->query('limit', 50), 200);
        $rows  = $this->leaderboardService->getGlobalLeaderboard($limit);

        return response()->json([
            'success' => true,
            'message' => 'Global leaderboard retrieved successfully.',
            'data'    => LeaderboardResource::collection($rows),
        ]);
    }

    public function daily(Request $request): JsonResponse
    {
        $limit = min((int) $request->query('limit', 50), 200);
        $rows  = $this->leaderboardService->getDailyLeaderboard($limit);

        return response()->json([
            'success' => true,
            'message' => 'Daily leaderboard retrieved successfully.',
            'data'    => LeaderboardResource::collection($rows),
        ]);
    }

    public function weekly(Request $request): JsonResponse
    {
        $limit = min((int) $request->query('limit', 50), 200);
        $rows  = $this->leaderboardService->getWeeklyLeaderboard($limit);

        return response()->json([
            'success' => true,
            'message' => 'Weekly leaderboard retrieved successfully.',
            'data'    => LeaderboardResource::collection($rows),
        ]);
    }

    public function monthly(Request $request): JsonResponse
    {
        $limit = min((int) $request->query('limit', 50), 200);
        $rows  = $this->leaderboardService->getMonthlyLeaderboard($limit);

        return response()->json([
            'success' => true,
            'message' => 'Monthly leaderboard retrieved successfully.',
            'data'    => LeaderboardResource::collection($rows),
        ]);
    }

    public function country(Request $request): JsonResponse
    {
        $request->validate(['country' => ['required', 'string', 'size:2']]);

        $limit = min((int) $request->query('limit', 50), 200);
        $rows  = $this->leaderboardService->getCountryLeaderboard(strtoupper((string) $request->query('country')), $limit);

        return response()->json([
            'success' => true,
            'message' => 'Country leaderboard retrieved successfully.',
            'data'    => LeaderboardResource::collection($rows),
        ]);
    }
}

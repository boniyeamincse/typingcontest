<?php

namespace App\Http\Controllers\API\Leaderboard;

use App\Http\Controllers\Controller;
use App\Http\Resources\Leaderboard\LeaderboardEntryResource;
use App\Models\Contest;
use App\Services\Leaderboard\LeaderboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaderboardController extends Controller
{
    public function __construct(private readonly LeaderboardService $leaderboardService) {}

    public function global(Request $request): JsonResponse
    {
        $rows = $this->leaderboardService->getGlobal($this->limit($request));

        return $this->success('Leaderboard fetched successfully', LeaderboardEntryResource::collection($rows));
    }

    public function topTen(Request $request): JsonResponse
    {
        $rows = $this->leaderboardService->getTopTen();

        return $this->success('Leaderboard fetched successfully', LeaderboardEntryResource::collection($rows));
    }

    public function contest(Request $request, int $contest): JsonResponse
    {
        if (!Contest::query()->whereKey($contest)->exists()) {
            return $this->error('Leaderboard not found', 404);
        }

        $rows = $this->leaderboardService->getContest($contest, $this->limit($request));

        return $this->success('Leaderboard fetched successfully', LeaderboardEntryResource::collection($rows));
    }

    public function daily(Request $request): JsonResponse
    {
        $rows = $this->leaderboardService->getDaily($this->limit($request));

        return $this->success('Leaderboard fetched successfully', LeaderboardEntryResource::collection($rows));
    }

    public function weekly(Request $request): JsonResponse
    {
        $rows = $this->leaderboardService->getWeekly($this->limit($request));

        return $this->success('Leaderboard fetched successfully', LeaderboardEntryResource::collection($rows));
    }

    public function monthly(Request $request): JsonResponse
    {
        $rows = $this->leaderboardService->getMonthly($this->limit($request));

        return $this->success('Leaderboard fetched successfully', LeaderboardEntryResource::collection($rows));
    }

    public function country(Request $request, string $countryCode): JsonResponse
    {
        $countryCode = strtoupper($countryCode);

        if (strlen($countryCode) !== 2) {
            return $this->error('Leaderboard not found', 404);
        }

        $rows = $this->leaderboardService->getCountry($countryCode, $this->limit($request));

        return $this->success('Leaderboard fetched successfully', LeaderboardEntryResource::collection($rows));
    }

    private function limit(Request $request): int
    {
        return max(1, min((int) $request->query('limit', 10), 200));
    }

    private function success(string $message, mixed $data): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ]);
    }

    private function error(string $message, int $status): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $status);
    }
}

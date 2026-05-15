<?php

namespace App\Http\Controllers\API\Analytics;

use App\Http\Controllers\Controller;
use App\Services\Analytics\AnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function __construct(private readonly AnalyticsService $service)
    {
    }

    public function dailySummary(Request $request): JsonResponse
    {
        $date = (string) $request->query('date', now()->toDateString());

        return response()->json($this->service->getDailySummary($date));
    }
}

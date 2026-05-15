<?php

namespace App\Http\Controllers\API\Typing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Typing\StartTypingSessionRequest;
use App\Http\Requests\Typing\SubmitTypingSessionRequest;
use App\Http\Requests\Typing\TypingUpdateRequest;
use App\Http\Resources\Typing\TypingHistoryResource;
use App\Http\Resources\Typing\TypingResultResource;
use App\Http\Resources\Typing\TypingSessionResource;
use App\Http\Resources\Typing\TypingStatusResource;
use App\Repositories\Typing\TypingResultRepositoryInterface;
use App\Repositories\Typing\TypingSessionRepositoryInterface;
use App\Services\Typing\TypingEngineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TypingController extends Controller
{
    public function __construct(
        private readonly TypingEngineService $typingEngineService,
        private readonly TypingSessionRepositoryInterface $typingSessionRepository,
        private readonly TypingResultRepositoryInterface $typingResultRepository,
    ) {}

    public function start(StartTypingSessionRequest $request): JsonResponse
    {
        $session = $this->typingEngineService->startSession($request->user(), [
            ...$request->validated(),
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Typing session started',
            'data' => new TypingSessionResource($session),
        ], 201);
    }

    public function update(TypingUpdateRequest $request): JsonResponse
    {
        $result = $this->typingEngineService->updateSession(
            $request->user(),
            (int) $request->validated('session_id'),
            [
                ...$request->validated(),
                'ip_address' => $request->ip(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Typing update accepted',
            'data' => [
                'session' => new TypingSessionResource($result['session']),
                'metrics' => $result['metrics'],
                'anti_cheat' => $result['anti_cheat'],
            ],
        ]);
    }

    public function submit(SubmitTypingSessionRequest $request): JsonResponse
    {
        $result = $this->typingEngineService->submitSession(
            $request->user(),
            (int) $request->validated('session_id'),
            [
                ...$request->validated(),
                'ip_address' => $request->ip(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Typing session submitted',
            'data' => [
                'session' => new TypingSessionResource($result['session']),
                'result' => new TypingResultResource($result['result']),
                'metrics' => $result['metrics'],
                'anti_cheat' => $result['anti_cheat'],
            ],
        ]);
    }

    public function show(int $id, Request $request): JsonResponse
    {
        $session = $this->typingSessionRepository->findById($id);

        if (!$session || $session->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Typing session not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Typing session fetched',
            'data' => new TypingSessionResource($session),
        ]);
    }

    public function status(int $sessionId, Request $request): JsonResponse
    {
        $status = $this->typingEngineService->getSessionStatus($request->user(), $sessionId);

        return response()->json([
            'success' => true,
            'message' => 'Typing status fetched',
            'data' => new TypingStatusResource($status),
        ]);
    }

    public function result(int $id, Request $request): JsonResponse
    {
        $result = $this->typingResultRepository->findById($id);

        if (!$result || $result->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Typing result not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Typing result fetched',
            'data' => new TypingResultResource($result),
        ]);
    }

    public function history(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 15), 50);
        $history = $this->typingSessionRepository->paginateUserHistory($request->user()->id, $perPage);

        return response()->json([
            'success' => true,
            'message' => 'Typing history fetched',
            'data' => TypingHistoryResource::collection($history->items()),
            'meta' => [
                'current_page' => $history->currentPage(),
                'last_page' => $history->lastPage(),
                'per_page' => $history->perPage(),
                'total' => $history->total(),
            ],
        ]);
    }
}

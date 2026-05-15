<?php

namespace App\Http\Controllers\API\Contest;

use App\Http\Controllers\Controller;
use App\Http\Requests\Contest\CreateContestRequest;
use App\Http\Requests\Contest\JoinContestRequest;
use App\Http\Requests\Contest\SubmitResultRequest;
use App\Http\Requests\Contest\UpdateContestRequest;
use App\Http\Resources\Contest\ContestDetailResource;
use App\Http\Resources\Contest\ContestResource;
use App\Http\Resources\Contest\ContestResultResource;
use App\Services\Contest\ContestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Contest;

class ContestController extends Controller
{
    public function __construct(private readonly ContestService $contestService) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 12), 50);
        $filters = $request->only(['type', 'status', 'search']);

        // Keep legacy behavior: guests only see published contests.
        if (!$request->user() && empty($filters['status'])) {
            $filters['status'] = Contest::STATUS_PUBLISHED;
        }

        $contests = $this->contestService->list($filters, $perPage);

        return response()->json([
            'success' => true,
            'message' => 'Contests retrieved successfully.',
            'data'    => ContestResource::collection(collect($contests->items())),
            'total'   => $contests->total(),
            'meta'    => [
                'current_page' => $contests->currentPage(),
                'last_page'    => $contests->lastPage(),
                'per_page'     => $contests->perPage(),
                'total'        => $contests->total(),
            ],
        ]);
    }

    public function show(Contest $contest): JsonResponse
    {
        $user = request()->user();

        if ($contest->status === Contest::STATUS_DRAFT) {
            $isAdmin = $user?->hasRole('admin') ?? false;
            $isOwner = $user && (int) $contest->created_by === (int) $user->id;

            if (!$isAdmin && !$isOwner) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contest not found.',
                ], 404);
            }
        }

        $contest->load(['creator:id,username,avatar', 'typingText', 'rule']);

        return response()->json([
            'success' => true,
            'message' => 'Contest retrieved successfully.',
            'data'    => new ContestDetailResource($contest),
        ]);
    }

    public function join(JoinContestRequest $request, Contest $contest): JsonResponse
    {
        $payload = $this->contestService->join($contest, $request->user(), [
            'ip_address'         => $request->ip(),
            'user_agent'         => (string) $request->userAgent(),
            'device_fingerprint' => $request->validated('device_fingerprint'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Joined contest successfully',
            'data'    => [
                'participant' => new ContestResultResource($payload['participant']->load('user:id,username,avatar,country')),
                'session'     => [
                    'id'            => $payload['session']->id,
                    'session_token' => $payload['session']->session_token,
                    'status'        => $payload['session']->status,
                    'joined_at'     => $payload['session']->joined_at?->toISOString(),
                ],
            ],
        ], 201);
    }

    public function getTypingText(Contest $contest): JsonResponse
    {
        if (!$contest->isActive() && $contest->status !== Contest::STATUS_FINISHED) {
            return response()->json([
                'success' => false,
                'message' => 'Typing text is only available during or after the contest.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Typing text retrieved successfully.',
            'data'    => [
                'contest_id'   => $contest->id,
                'text_content' => $contest->getTypingContent(),
            ],
        ]);
    }

    public function submit(SubmitResultRequest $request, Contest $contest): JsonResponse
    {
        $result = $this->contestService->submitResult($contest, $request->user(), $request->validated())
            ->load('user:id,username,avatar,country');

        return response()->json([
            'success' => true,
            'message' => 'Result submitted successfully.',
            'result'  => new ContestResultResource($result),
            'data'    => new ContestResultResource($result),
        ]);
    }

    public function getUserResult(Request $request, Contest $contest): JsonResponse
    {
        $result = $contest->participants()
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Result not found for current user.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Result retrieved successfully.',
            'data'    => new ContestResultResource($result->load('user:id,username,avatar,country')),
        ]);
    }

    public function store(CreateContestRequest $request): JsonResponse
    {
        $contest = $this->contestService->create($request->validated(), $request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Contest created successfully.',
            'contest' => new ContestDetailResource($contest->load(['creator:id,username,avatar', 'typingText', 'rule'])),
            'data'    => new ContestDetailResource($contest->load(['creator:id,username,avatar', 'typingText', 'rule'])),
        ], 201);
    }

    public function update(UpdateContestRequest $request, Contest $contest): JsonResponse
    {
        $contest = $this->contestService->update($contest, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Contest updated successfully.',
            'data'    => new ContestDetailResource($contest->load(['creator:id,username,avatar', 'typingText', 'rule'])),
        ]);
    }

    public function destroy(Contest $contest): JsonResponse
    {
        $this->contestService->delete($contest);

        return response()->json([
            'success' => true,
            'message' => 'Contest deleted successfully.',
            'data'    => null,
        ]);
    }

    public function publish(Contest $contest): JsonResponse
    {
        $contest = $this->contestService->publish($contest);

        return response()->json([
            'success' => true,
            'message' => 'Contest published successfully.',
            'contest' => new ContestDetailResource($contest->load(['creator:id,username,avatar', 'typingText', 'rule'])),
            'data'    => new ContestDetailResource($contest->load(['creator:id,username,avatar', 'typingText', 'rule'])),
        ]);
    }

    public function start(Contest $contest): JsonResponse
    {
        $contest = $this->contestService->start($contest);

        return response()->json([
            'success' => true,
            'message' => 'Contest started successfully.',
            'data'    => new ContestDetailResource($contest->load(['creator:id,username,avatar', 'typingText', 'rule'])),
        ]);
    }

    public function end(Contest $contest): JsonResponse
    {
        $contest = $this->contestService->end($contest);

        return response()->json([
            'success' => true,
            'message' => 'Contest ended successfully.',
            'data'    => new ContestDetailResource($contest->load(['creator:id,username,avatar', 'typingText', 'rule'])),
        ]);
    }

    public function pause(Contest $contest): JsonResponse
    {
        $contest = $this->contestService->pause($contest);

        return response()->json([
            'success' => true,
            'message' => 'Contest paused successfully.',
            'data'    => new ContestDetailResource($contest->load(['creator:id,username,avatar', 'typingText', 'rule'])),
        ]);
    }

    public function resume(Contest $contest): JsonResponse
    {
        $contest = $this->contestService->resume($contest);

        return response()->json([
            'success' => true,
            'message' => 'Contest resumed successfully.',
            'data'    => new ContestDetailResource($contest->load(['creator:id,username,avatar', 'typingText', 'rule'])),
        ]);
    }

    public function cancel(Contest $contest): JsonResponse
    {
        $contest = $this->contestService->cancel($contest);

        return response()->json([
            'success' => true,
            'message' => 'Contest cancelled successfully.',
            'data'    => new ContestDetailResource($contest->load(['creator:id,username,avatar', 'typingText', 'rule'])),
        ]);
    }
}

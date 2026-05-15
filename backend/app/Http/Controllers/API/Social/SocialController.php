<?php

namespace App\Http\Controllers\API\Social;

use App\Http\Controllers\Controller;
use App\Services\Social\FeedService;
use App\Services\Social\SocialGraphService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SocialController extends Controller
{
    public function __construct(
        private readonly SocialGraphService $socialGraphService,
        private readonly FeedService $feedService,
    ) {
    }

    public function follow(Request $request): JsonResponse
    {
        $payload = $request->validate(['user_id' => ['required', 'integer', 'exists:users,id']]);

        $ok = $this->socialGraphService->follow((int) $request->user()->id, (int) $payload['user_id']);

        return response()->json([
            'success' => $ok,
            'message' => $ok ? 'User followed successfully' : 'Unable to follow user',
            'data' => ['followed_user_id' => (int) $payload['user_id']],
        ], $ok ? 200 : 422);
    }

    public function unfollow(Request $request): JsonResponse
    {
        $payload = $request->validate(['user_id' => ['required', 'integer', 'exists:users,id']]);

        $ok = $this->socialGraphService->unfollow((int) $request->user()->id, (int) $payload['user_id']);

        return response()->json([
            'success' => $ok,
            'message' => $ok ? 'User unfollowed successfully' : 'Unable to unfollow user',
            'data' => ['unfollowed_user_id' => (int) $payload['user_id']],
        ], $ok ? 200 : 422);
    }

    public function sendFriendRequest(Request $request): JsonResponse
    {
        $payload = $request->validate(['user_id' => ['required', 'integer', 'exists:users,id']]);

        $ok = $this->socialGraphService->sendFriendRequest((int) $request->user()->id, (int) $payload['user_id']);

        return response()->json([
            'success' => $ok,
            'message' => $ok ? 'Friend request sent successfully' : 'Unable to send friend request',
            'data' => ['receiver_user_id' => (int) $payload['user_id']],
        ], $ok ? 200 : 422);
    }

    public function respondFriendRequest(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'request_id' => ['required', 'integer', 'exists:friend_requests,id'],
            'status' => ['required', 'in:accepted,rejected'],
        ]);

        $ok = $this->socialGraphService->respondFriendRequest(
            (int) $payload['request_id'],
            (int) $request->user()->id,
            (string) $payload['status']
        );

        return response()->json([
            'success' => $ok,
            'message' => $ok ? 'Friend request updated successfully' : 'Unable to update friend request',
            'data' => [
                'request_id' => (int) $payload['request_id'],
                'status' => (string) $payload['status'],
            ],
        ], $ok ? 200 : 422);
    }

    public function feed(Request $request): JsonResponse
    {
        $limit = max(1, min((int) $request->query('limit', 20), 100));
        $rows = $this->feedService->getUserFeed((int) $request->user()->id, $limit);

        return response()->json([
            'success' => true,
            'message' => 'Activity feed fetched successfully',
            'data' => $rows,
        ]);
    }
}

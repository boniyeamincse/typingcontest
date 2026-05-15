<?php

namespace App\Http\Controllers\API\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\FeatureBadgeRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Requests\Profile\UploadAvatarRequest;
use App\Http\Requests\Profile\UploadCoverRequest;
use App\Http\Resources\ActivityResource;
use App\Http\Resources\BadgeResource;
use App\Http\Resources\MatchHistoryResource;
use App\Http\Resources\ProfileResource;
use App\Http\Resources\PublicProfileResource;
use App\Http\Resources\StatisticResource;
use App\Services\Profile\AvatarService;
use App\Services\Profile\ProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="Profile", description="User profile management")
 */
class ProfileController extends Controller
{
    public function __construct(
        private readonly ProfileService $profileService,
        private readonly AvatarService  $avatarService,
    ) {}

    /**
     * @OA\Get(
     *   path="/api/v1/profile",
     *   summary="Get authenticated user profile",
     *   tags={"Profile"},
     *   security={{"sanctum":{}}},
     *   @OA\Response(response=200, description="Profile data")
     * )
     */
    public function show(Request $request): JsonResponse
    {
        $user = $this->profileService->getMyProfile($request->user());

        return response()->json([
            'success' => true,
            'message' => 'Profile retrieved successfully.',
            'data'    => new ProfileResource($user),
        ]);
    }

    /**
     * @OA\Get(
     *   path="/api/v1/profile/{username}",
     *   summary="Get public profile by username",
     *   tags={"Profile"},
     *   @OA\Parameter(name="username", in="path", required=true, @OA\Schema(type="string")),
     *   @OA\Response(response=200, description="Public profile"),
     *   @OA\Response(response=404, description="User not found")
     * )
     */
    public function showPublic(string $username): JsonResponse
    {
        $user = $this->profileService->getPublicProfile($username);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Public profile retrieved successfully.',
            'data'    => new PublicProfileResource($user),
        ]);
    }

    /**
     * @OA\Put(
     *   path="/api/v1/profile/update",
     *   summary="Update authenticated user profile",
     *   tags={"Profile"},
     *   security={{"sanctum":{}}},
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/UpdateProfileRequest")),
     *   @OA\Response(response=200, description="Profile updated")
     * )
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $this->profileService->updateProfile(
            $request->user(),
            $request->validated(),
            $request->ip()
        );

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'data'    => new ProfileResource($user),
        ]);
    }

    /**
     * @OA\Post(
     *   path="/api/v1/profile/avatar",
     *   summary="Upload profile avatar",
     *   tags={"Profile"},
     *   security={{"sanctum":{}}},
     *   @OA\RequestBody(required=true,
     *     @OA\MediaType(mediaType="multipart/form-data",
     *       @OA\Schema(@OA\Property(property="avatar", type="string", format="binary"))
     *     )
     *   ),
     *   @OA\Response(response=200, description="Avatar uploaded")
     * )
     */
    public function uploadAvatar(UploadAvatarRequest $request): JsonResponse
    {
        $url = $this->profileService->uploadAvatar(
            $request->user(),
            $request->file('avatar'),
            $request->ip()
        );

        return response()->json([
            'success' => true,
            'message' => 'Avatar uploaded successfully.',
            'data'    => ['avatar_url' => $url],
        ]);
    }

    /**
     * @OA\Post(
     *   path="/api/v1/profile/cover",
     *   summary="Upload profile cover photo",
     *   tags={"Profile"},
     *   security={{"sanctum":{}}},
     *   @OA\Response(response=200, description="Cover photo uploaded")
     * )
     */
    public function uploadCover(UploadCoverRequest $request): JsonResponse
    {
        $url = $this->avatarService->storeCover($request->user(), $request->file('cover'));

        return response()->json([
            'success' => true,
            'message' => 'Cover photo uploaded successfully.',
            'data'    => ['cover_photo_url' => $url],
        ]);
    }

    /**
     * @OA\Get(
     *   path="/api/v1/profile/stats",
     *   summary="Get authenticated user typing statistics",
     *   tags={"Profile"},
     *   security={{"sanctum":{}}},
     *   @OA\Response(response=200, description="Statistics data")
     * )
     */
    public function stats(Request $request): JsonResponse
    {
        $statistics = $this->profileService->getStats($request->user());

        return response()->json([
            'success' => true,
            'message' => 'Statistics retrieved successfully.',
            'data'    => new StatisticResource($statistics),
        ]);
    }

    /**
     * @OA\Get(
     *   path="/api/v1/profile/badges",
     *   summary="Get user badges",
     *   tags={"Profile"},
     *   security={{"sanctum":{}}},
     *   @OA\Response(response=200, description="Badges list")
     * )
     */
    public function badges(Request $request): JsonResponse
    {
        $badges = $this->profileService->getBadges($request->user());

        return response()->json([
            'success' => true,
            'message' => 'Badges retrieved successfully.',
            'data'    => BadgeResource::collection($badges),
        ]);
    }

    /**
     * @OA\Post(
     *   path="/api/v1/profile/badges/feature",
     *   summary="Feature a badge on profile",
     *   tags={"Profile"},
     *   security={{"sanctum":{}}},
     *   @OA\RequestBody(required=true, @OA\JsonContent(@OA\Property(property="badge_id", type="integer"))),
     *   @OA\Response(response=200, description="Badge featured")
     * )
     */
    public function featureBadge(FeatureBadgeRequest $request): JsonResponse
    {
        $badgeId = $request->validated('badge_id');

        // Ensure user owns this badge
        $owned = $request->user()->badges()->where('badges.id', $badgeId)->exists();
        if (!$owned) {
            return response()->json([
                'success' => false,
                'message' => 'You have not earned this badge.',
            ], 403);
        }

        $this->profileService->featureBadge($request->user(), $badgeId);

        return response()->json([
            'success' => true,
            'message' => 'Badge featured on profile.',
            'data'    => null,
        ]);
    }

    /**
     * @OA\Get(
     *   path="/api/v1/profile/matches",
     *   summary="Get match history",
     *   tags={"Profile"},
     *   security={{"sanctum":{}}},
     *   @OA\Response(response=200, description="Paginated match history")
     * )
     */
    public function matches(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 15), 50);
        $history = $this->profileService->getMatchHistory($request->user(), $perPage);

        return response()->json([
            'success' => true,
            'message' => 'Match history retrieved successfully.',
            'data'    => MatchHistoryResource::collection($history),
            'meta'    => [
                'current_page' => $history->currentPage(),
                'last_page'    => $history->lastPage(),
                'per_page'     => $history->perPage(),
                'total'        => $history->total(),
            ],
        ]);
    }

    /**
     * @OA\Get(
     *   path="/api/v1/profile/activity",
     *   summary="Get user activity feed",
     *   tags={"Profile"},
     *   security={{"sanctum":{}}},
     *   @OA\Response(response=200, description="Paginated activity log")
     * )
     */
    public function activity(Request $request): JsonResponse
    {
        $perPage  = min((int) $request->query('per_page', 20), 100);
        $activity = $this->profileService->getActivity($request->user(), $perPage);

        return response()->json([
            'success' => true,
            'message' => 'Activity retrieved successfully.',
            'data'    => ActivityResource::collection($activity),
            'meta'    => [
                'current_page' => $activity->currentPage(),
                'last_page'    => $activity->lastPage(),
                'per_page'     => $activity->perPage(),
                'total'        => $activity->total(),
            ],
        ]);
    }
}

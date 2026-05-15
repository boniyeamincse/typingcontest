<?php

namespace App\Http\Controllers;

use App\Models\Contest;
use App\Models\Result;
use App\Models\TypingText;
use App\Models\Leaderboard;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ContestController extends Controller
{
    // ── Public List & Details ────────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $query = Contest::whereIn('status', ['published', 'active'])
            ->orderByRaw("FIELD(status,'active','published')")
            ->orderBy('start_time', 'asc');

        if ($request->query('type')) {
            $query->where('type', $request->query('type'));
        }

        $contests = $query->paginate(12);

        return response()->json($contests);
    }

    public function show(Contest $contest): JsonResponse
    {
        if (!in_array($contest->status, ['published', 'active', 'finished'])) {
            abort(404);
        }

        return response()->json([
            'contest' => $contest,
            'participant_count' => Result::where('contest_id', $contest->id)->count(),
            'typing_text' => $contest->typingText,
        ]);
    }

    // ── Contest Participation ────────────────────────────────────────────────

    public function join(Request $request, Contest $contest): JsonResponse
    {
        $user = auth()->user();

        // Validate contest is open for joining
        if (!in_array($contest->status, ['published', 'active'])) {
            throw ValidationException::withMessages([
                'contest' => ['This contest is not open for joining.'],
            ]);
        }

        // Check if user already joined
        if (Result::where('contest_id', $contest->id)->where('user_id', $user->id)->exists()) {
            throw ValidationException::withMessages([
                'contest' => ['You have already joined this contest.'],
            ]);
        }

        // Check if user is banned
        if ($user->is_banned) {
            throw ValidationException::withMessages([
                'user' => ['Your account has been banned.'],
            ]);
        }

        // Create participation record
        $result = Result::create([
            'contest_id' => $contest->id,
            'user_id' => $user->id,
        ]);

        return response()->json([
            'message' => 'Joined contest successfully',
            'participant_id' => $result->id,
        ], 201);
    }

    public function getTypingText(Contest $contest): JsonResponse
    {
        // Only allow access if contest is active
        if ($contest->status !== 'active') {
            abort(403, 'Contest not yet started');
        }

        // Check if user joined
        $result = Result::where('contest_id', $contest->id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return response()->json([
            'typing_text' => $contest->typingText->content,
            'word_count' => $contest->typingText->word_count,
            'difficulty' => $contest->typingText->difficulty,
            'language' => $contest->typingText->language,
        ]);
    }

    public function submit(Request $request, Contest $contest): JsonResponse
    {
        $user = auth()->user();

        $data = $request->validate([
            'wpm' => ['required', 'integer', 'min:0', 'max:500'],
            'accuracy' => ['required', 'numeric', 'between:0,100'],
            'errors' => ['required', 'integer', 'min:0'],
            'keystroke_timings' => ['sometimes', 'array'],
        ]);

        $result = Result::where('contest_id', $contest->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Update result with typing data
        $result->update([
            'wpm' => $data['wpm'],
            'accuracy' => $data['accuracy'],
            'errors' => $data['errors'],
            'score' => $result->calculateScore(),
            'submitted_at' => now(),
        ]);

        return response()->json([
            'message' => 'Result submitted successfully',
            'result' => $result,
        ]);
    }

    public function getUserResult(Contest $contest): JsonResponse
    {
        $result = Result::where('contest_id', $contest->id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return response()->json($result);
    }

    // ── Leaderboards ─────────────────────────────────────────────────────────

    public function leaderboard(Contest $contest): JsonResponse
    {
        $leaderboard = Result::where('contest_id', $contest->id)
            ->orderBy('score', 'desc')
            ->orderBy('accuracy', 'desc')
            ->limit(50)
            ->with('user:id,username,avatar,country')
            ->get()
            ->map(function ($result, $index) {
                return [
                    'rank' => $index + 1,
                    'user' => $result->user,
                    'wpm' => $result->wpm,
                    'accuracy' => $result->accuracy,
                    'score' => $result->score,
                ];
            });

        return response()->json(['leaderboard' => $leaderboard]);
    }

    public function globalLeaderboard(Request $request): JsonResponse
    {
        $page = $request->query('page', 1);
        $limit = 50;

        $leaderboard = Leaderboard::where('type', 'global')
            ->where('period_key', 'all-time')
            ->orderBy('rank', 'asc')
            ->with('user:id,username,avatar,country')
            ->paginate($limit, ['*'], 'page', $page);

        return response()->json($leaderboard);
    }

    public function dailyLeaderboard(): JsonResponse
    {
        $today = now()->format('Y-m-d');

        $leaderboard = Leaderboard::where('type', 'daily')
            ->where('period_key', $today)
            ->orderBy('rank', 'asc')
            ->limit(50)
            ->with('user:id,username,avatar,country')
            ->get();

        return response()->json(['leaderboard' => $leaderboard]);
    }

    public function weeklyLeaderboard(): JsonResponse
    {
        $weekKey = now()->format('Y-\\WW');

        $leaderboard = Leaderboard::where('type', 'weekly')
            ->where('period_key', $weekKey)
            ->orderBy('rank', 'asc')
            ->limit(50)
            ->with('user:id,username,avatar,country')
            ->get();

        return response()->json(['leaderboard' => $leaderboard]);
    }

    public function monthlyLeaderboard(): JsonResponse
    {
        $monthKey = now()->format('Y-m');

        $leaderboard = Leaderboard::where('type', 'monthly')
            ->where('period_key', $monthKey)
            ->orderBy('rank', 'asc')
            ->limit(50)
            ->with('user:id,username,avatar,country')
            ->get();

        return response()->json(['leaderboard' => $leaderboard]);
    }

    public function countryLeaderboard(): JsonResponse
    {
        $leaderboard = \App\Models\CountryRanking::where('period_key', 'global')
            ->orderBy('rank', 'asc')
            ->get();

        return response()->json(['leaderboard' => $leaderboard]);
    }

    // ── User Profile ─────────────────────────────────────────────────────────

    public function getUserProfile($username): JsonResponse
    {
        $user = User::where('username', $username)->firstOrFail();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'avatar' => $user->avatar,
                'country' => $user->country,
                'global_rank' => $user->global_rank,
                'total_wpm' => $user->total_wpm,
                'accuracy_avg' => $user->accuracy_avg,
                'xp_points' => $user->xp_points,
            ],
            'badges' => $user->badges()->get(['badges.name', 'badges.icon_url']),
        ]);
    }

    public function getContestHistory(): JsonResponse
    {
        $user = auth()->user();

        $limit = $user->plan_type === 'pro' ? 100 : 10;

        $history = Result::where('user_id', $user->id)
            ->whereNotNull('submitted_at')
            ->with('contest')
            ->orderBy('submitted_at', 'desc')
            ->limit($limit)
            ->get();

        return response()->json(['history' => $history]);
    }

    public function getUserStats(): JsonResponse
    {
        $user = auth()->user();

        $stats = [
            'total_contests' => Result::where('user_id', $user->id)->count(),
            'avg_wpm' => Result::where('user_id', $user->id)->avg('wpm'),
            'avg_accuracy' => Result::where('user_id', $user->id)->avg('accuracy'),
            'best_score' => Result::where('user_id', $user->id)->max('score'),
            'xp_points' => $user->xp_points,
            'global_rank' => $user->global_rank,
        ];

        return response()->json($stats);
    }

    public function getUserBadges(): JsonResponse
    {
        $user = auth()->user();

        $badges = $user->badges()->get(['badges.id', 'badges.name', 'badges.slug', 'badges.icon_url', 'badges.description']);

        return response()->json(['badges' => $badges]);
    }

    // ── Admin Operations ─────────────────────────────────────────────────────

    public function adminList(Request $request): JsonResponse
    {
        $query = Contest::query();

        if ($request->query('status')) {
            $query->where('status', $request->query('status'));
        }

        $contests = $query->paginate(20);

        return response()->json($contests);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:daily,weekly,monthly,special'],
            'typing_text_id' => ['required', 'exists:typing_texts,id'],
            'max_participants' => ['sometimes', 'integer', 'min:1'],
            'prize_description' => ['sometimes', 'string'],
            'start_time' => ['required', 'date', 'after:now'],
            'end_time' => ['required', 'date', 'after:start_time'],
        ]);

        $contest = Contest::create([
            ...$data,
            'created_by' => auth()->id(),
            'status' => 'draft',
        ]);

        return response()->json([
            'message' => 'Contest created successfully',
            'contest' => $contest,
        ], 201);
    }

    public function update(Request $request, Contest $contest): JsonResponse
    {
        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'type' => ['sometimes', 'in:daily,weekly,monthly,special'],
            'max_participants' => ['sometimes', 'integer', 'min:1'],
            'prize_description' => ['sometimes', 'string'],
            'start_time' => ['sometimes', 'date'],
            'end_time' => ['sometimes', 'date'],
        ]);

        $contest->update($data);

        return response()->json([
            'message' => 'Contest updated successfully',
            'contest' => $contest,
        ]);
    }

    public function destroy(Contest $contest): JsonResponse
    {
        if ($contest->status !== 'draft') {
            throw ValidationException::withMessages([
                'contest' => ['Only draft contests can be deleted.'],
            ]);
        }

        $contest->delete();

        return response()->json(['message' => 'Contest deleted successfully']);
    }

    public function publish(Contest $contest): JsonResponse
    {
        $contest->update(['status' => 'published']);

        return response()->json([
            'message' => 'Contest published successfully',
            'contest' => $contest,
        ]);
    }

    public function cancel(Contest $contest): JsonResponse
    {
        $contest->update(['status' => 'cancelled']);

        return response()->json([
            'message' => 'Contest cancelled successfully',
        ]);
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    private function recalculateRanks(int $contestId): void
    {
        $results = Result::where('contest_id', $contestId)
            ->where('wpm', '>', 0)
            ->orderBy('score', 'desc')
            ->orderBy('accuracy', 'desc')
            ->get();

        foreach ($results as $rank => $result) {
            $result->update(['rank' => $rank + 1]);
        }
    }
}

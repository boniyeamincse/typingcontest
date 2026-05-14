<?php

namespace App\Http\Controllers;

use App\Models\Contest;
use App\Models\Result;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ContestController extends Controller
{
    // ── Public / user endpoints ──────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $query = Contest::published()
            ->orderByRaw("FIELD(status,'active','published','completed')")
            ->orderBy('starts_at', 'asc');

        if ($request->query('type')) {
            $query->where('type', $request->query('type'));
        }

        if ($request->query('status')) {
            $query->where('status', $request->query('status'));
        }

        $contests = $query->paginate(12);

        return response()->json($contests);
    }

    public function show(Contest $contest): JsonResponse
    {
        if ($contest->status === 'draft') {
            abort(404);
        }

        return response()->json($contest);
    }

    public function join(Request $request, Contest $contest): JsonResponse
    {
        if (! in_array($contest->status, ['published', 'active'], true)) {
            throw ValidationException::withMessages([
                'contest' => ['This contest is not open for joining.'],
            ]);
        }

        $alreadyJoined = Result::where('user_id', $request->user()->id)
            ->where('contest_id', $contest->id)
            ->exists();

        if ($alreadyJoined) {
            return response()->json([
                'message' => 'Already joined.',
                'contest' => $contest,
            ]);
        }

        Result::create([
            'user_id'    => $request->user()->id,
            'contest_id' => $contest->id,
            'wpm'        => 0,
            'accuracy'   => 0,
            'errors'     => 0,
        ]);

        return response()->json([
            'message' => 'Joined successfully.',
            'contest' => $contest,
        ]);
    }

    public function submit(Request $request, Contest $contest): JsonResponse
    {
        if ($contest->status !== 'active') {
            throw ValidationException::withMessages([
                'contest' => ['Submissions are closed for this contest.'],
            ]);
        }

        $data = $request->validate([
            'wpm'      => ['required', 'integer', 'min:0', 'max:300'],
            'accuracy' => ['required', 'numeric', 'min:0', 'max:100'],
            'errors'   => ['required', 'integer', 'min:0'],
        ]);

        $result = Result::where('user_id', $request->user()->id)
            ->where('contest_id', $contest->id)
            ->first();

        if (! $result) {
            throw ValidationException::withMessages([
                'contest' => ['You have not joined this contest.'],
            ]);
        }

        if ($result->wpm > 0) {
            throw ValidationException::withMessages([
                'contest' => ['You have already submitted a result.'],
            ]);
        }

        $result->update($data);

        $this->recalculateRanks($contest->id);

        $result->refresh();

        return response()->json([
            'message' => 'Result submitted.',
            'result'  => $result,
        ]);
    }

    public function leaderboard(Contest $contest): JsonResponse
    {
        $results = Result::with('user:id,name')
            ->where('contest_id', $contest->id)
            ->where('wpm', '>', 0)
            ->orderBy('score', 'desc')
            ->orderBy('errors', 'asc')
            ->limit(50)
            ->get();

        return response()->json([
            'contest' => $contest,
            'leaderboard' => $results,
        ]);
    }

    public function globalLeaderboard(): JsonResponse
    {
        $results = Result::with(['user:id,name', 'contest:id,title,type'])
            ->where('wpm', '>', 0)
            ->orderBy('score', 'desc')
            ->limit(50)
            ->get();

        return response()->json($results);
    }

    // ── Admin endpoints ──────────────────────────────────────────────────────

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title'            => ['required', 'string', 'max:255'],
            'type'             => ['required', 'in:daily,weekly,monthly,special'],
            'text_content'     => ['required', 'string', 'min:20'],
            'duration_seconds' => ['required', 'integer', 'min:15', 'max:600'],
            'starts_at'        => ['nullable', 'date'],
            'ends_at'          => ['nullable', 'date', 'after:starts_at'],
        ]);

        $contest = Contest::create([
            ...$data,
            'status'     => 'draft',
            'created_by' => $request->user()->id,
        ]);

        return response()->json($contest, 201);
    }

    public function update(Request $request, Contest $contest): JsonResponse
    {
        $data = $request->validate([
            'title'            => ['sometimes', 'string', 'max:255'],
            'type'             => ['sometimes', 'in:daily,weekly,monthly,special'],
            'text_content'     => ['sometimes', 'string', 'min:20'],
            'duration_seconds' => ['sometimes', 'integer', 'min:15', 'max:600'],
            'starts_at'        => ['nullable', 'date'],
            'ends_at'          => ['nullable', 'date'],
        ]);

        $contest->update($data);

        return response()->json($contest);
    }

    public function publish(Contest $contest): JsonResponse
    {
        if ($contest->status !== 'draft') {
            throw ValidationException::withMessages([
                'contest' => ['Only draft contests can be published.'],
            ]);
        }

        $contest->update(['status' => 'published']);

        return response()->json(['message' => 'Contest published.', 'contest' => $contest]);
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    private function recalculateRanks(int $contestId): void
    {
        $results = Result::where('contest_id', $contestId)
            ->where('wpm', '>', 0)
            ->orderBy('score', 'desc')
            ->orderBy('errors', 'asc')
            ->get();

        foreach ($results as $rank => $result) {
            $result->update(['rank' => $rank + 1]);
        }
    }
}

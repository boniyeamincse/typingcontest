<?php

namespace App\Repositories\Contest;

use App\Models\Contest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class EloquentContestRepository implements ContestRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $query = Contest::with(['creator:id,username,avatar', 'rule'])
            ->orderByRaw("FIELD(status, 'active', 'published', 'finished', 'draft', 'cancelled')")
            ->orderBy('start_time', 'asc');

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $query->where('title', 'like', '%' . $filters['search'] . '%');
        }

        return $query->paginate($perPage);
    }

    public function findById(int $id): ?Contest
    {
        return Contest::with(['creator:id,username,avatar', 'typingText', 'rule'])->find($id);
    }

    public function findBySlug(string $slug): ?Contest
    {
        return Contest::where('slug', $slug)->with(['typingText', 'rule'])->first();
    }

    public function create(array $data): Contest
    {
        $data['slug'] = $data['slug'] ?? Str::slug($data['title']) . '-' . Str::random(6);

        return Contest::create($data);
    }

    public function update(Contest $contest, array $data): Contest
    {
        if (isset($data['title']) && !isset($data['slug'])) {
            $data['slug'] = Str::slug($data['title']) . '-' . Str::random(6);
        }

        $contest->fill($data)->save();

        return $contest->fresh(['creator', 'typingText', 'rule']);
    }

    public function delete(Contest $contest): void
    {
        $contest->delete();
    }

    public function getActiveContests(): Collection
    {
        return Contest::where('status', Contest::STATUS_ACTIVE)
            ->where('is_paused', false)
            ->with('rule')
            ->get();
    }

    public function getScheduledContests(): Collection
    {
        return Contest::where('status', Contest::STATUS_PUBLISHED)
            ->where('start_time', '<=', now())
            ->with('rule')
            ->get();
    }

    public function activate(Contest $contest): Contest
    {
        $contest->update([
            'status'     => Contest::STATUS_ACTIVE,
            'started_at' => now(),
            'is_paused'  => false,
        ]);

        return $contest->fresh();
    }

    public function finish(Contest $contest): Contest
    {
        $contest->update([
            'status'   => Contest::STATUS_FINISHED,
            'ended_at' => now(),
        ]);

        return $contest->fresh();
    }

    public function setPaused(Contest $contest, bool $paused): Contest
    {
        $contest->update(['is_paused' => $paused]);

        return $contest->fresh();
    }

    public function cancel(Contest $contest): Contest
    {
        $contest->update(['status' => Contest::STATUS_CANCELLED]);

        return $contest->fresh();
    }
}

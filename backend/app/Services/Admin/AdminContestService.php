<?php

namespace App\Services\Admin;

use App\Models\Contest;
use App\Repositories\Admin\AdminContestRepositoryInterface;

class AdminContestService
{
    public function __construct(
        private readonly AdminContestRepositoryInterface $repository,
        private readonly AdminAuditService $auditService,
    ) {
    }

    public function list(array $filters)
    {
        return $this->repository->paginate($filters);
    }

    public function create(array $payload): Contest
    {
        return Contest::create($payload);
    }

    public function update(Contest $contest, array $payload): Contest
    {
        $contest->update($payload);

        return $contest->refresh();
    }

    public function delete(Contest $contest): void
    {
        $contest->delete();
    }

    public function start(Contest $contest): Contest
    {
        $contest->update([
            'status' => Contest::STATUS_ACTIVE,
            'started_at' => now(),
        ]);

        return $contest->refresh();
    }

    public function stop(Contest $contest): Contest
    {
        $contest->update([
            'status' => Contest::STATUS_FINISHED,
            'ended_at' => now(),
        ]);

        return $contest->refresh();
    }

    public function cloneContest(Contest $contest): Contest
    {
        $copy = $contest->replicate(['slug', 'status', 'started_at', 'ended_at']);
        $copy->title = $contest->title.' (Clone)';
        $copy->status = Contest::STATUS_DRAFT;
        $copy->start_time = now()->addDay();
        $copy->end_time = now()->addDay()->addMinutes((int) $contest->duration_minutes);
        $copy->save();

        return $copy;
    }

    public function analytics(Contest $contest): array
    {
        $results = $contest->results();

        return [
            'contest_id' => $contest->id,
            'participants' => $results->count(),
            'avg_wpm' => round((float) $results->avg('wpm'), 2),
            'avg_accuracy' => round((float) $results->avg('accuracy'), 2),
            'max_wpm' => (int) $results->max('wpm'),
        ];
    }
}

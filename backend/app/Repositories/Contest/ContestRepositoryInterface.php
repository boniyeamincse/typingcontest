<?php

namespace App\Repositories\Contest;

use App\Models\Contest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ContestRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 12): LengthAwarePaginator;

    public function findById(int $id): ?Contest;

    public function findBySlug(string $slug): ?Contest;

    public function create(array $data): Contest;

    public function update(Contest $contest, array $data): Contest;

    public function delete(Contest $contest): void;

    public function getActiveContests(): Collection;

    public function getScheduledContests(): Collection;

    /** Mark contest as active, set started_at */
    public function activate(Contest $contest): Contest;

    /** Mark contest as finished, set ended_at */
    public function finish(Contest $contest): Contest;

    /** Pause or resume a contest */
    public function setPaused(Contest $contest, bool $paused): Contest;

    public function cancel(Contest $contest): Contest;
}

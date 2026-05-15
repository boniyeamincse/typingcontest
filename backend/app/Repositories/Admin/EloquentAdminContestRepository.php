<?php

namespace App\Repositories\Admin;

use App\Models\Contest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentAdminContestRepository implements AdminContestRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $perPage = max(10, min(100, (int) ($filters['per_page'] ?? 20)));

        return Contest::query()
            ->with(['creator:id,name', 'typingText:id,language,difficulty'])
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['type'] ?? null, fn ($q, $type) => $q->where('type', $type))
            ->orderByDesc('start_time')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): Contest
    {
        return Contest::query()->findOrFail($id);
    }
}

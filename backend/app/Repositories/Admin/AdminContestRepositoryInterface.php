<?php

namespace App\Repositories\Admin;

use App\Models\Contest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AdminContestRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator;

    public function findOrFail(int $id): Contest;
}

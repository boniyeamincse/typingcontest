<?php

namespace App\Repositories\Admin;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AdminUserRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator;

    public function suspicious(array $filters): LengthAwarePaginator;
}

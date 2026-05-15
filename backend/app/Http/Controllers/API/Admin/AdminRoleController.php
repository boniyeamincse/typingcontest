<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\API\Admin\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;

class AdminRoleController extends Controller
{
    use RespondsWithApi;

    public function index()
    {
        return $this->success('Action completed successfully', Role::query()->where('guard_name', 'api')->get());
    }

    public function assign(Request $request)
    {
        Gate::authorize('manage-admin-roles');

        $payload = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['string'],
        ]);

        $user = User::query()->findOrFail($payload['user_id']);
        $user->syncRoles($payload['roles']);

        return $this->success('Action completed successfully', $user->load('roles:id,name'));
    }
}

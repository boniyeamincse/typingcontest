<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\API\Admin\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Admin\AdminBadgeRewardService;
use Illuminate\Http\Request;

class AdminBadgeRewardController extends Controller
{
    use RespondsWithApi;

    public function __construct(private readonly AdminBadgeRewardService $service)
    {
    }

    public function index()
    {
        return $this->success('Action completed successfully', $this->service->listBadges());
    }

    public function store(Request $request)
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:120', 'unique:badges,slug'],
            'description' => ['nullable', 'string'],
            'icon_url' => ['nullable', 'string', 'max:255'],
            'requirement_type' => ['required', 'string', 'max:60'],
            'requirement_value' => ['required', 'integer', 'min:0'],
            'is_premium' => ['nullable', 'boolean'],
        ]);

        return $this->success('Action completed successfully', $this->service->createBadge($payload), 201);
    }

    public function assign(Request $request)
    {
        $payload = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'badge_id' => ['required', 'integer', 'exists:badges,id'],
        ]);

        $user = User::query()->findOrFail($payload['user_id']);
        $this->service->assignBadge($user, (int) $payload['badge_id']);

        return $this->success('Action completed successfully', []);
    }

    public function xpRule(Request $request)
    {
        $payload = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'xp' => ['required', 'integer', 'min:1', 'max:10000'],
        ]);

        $user = User::query()->findOrFail($payload['user_id']);

        return $this->success('Action completed successfully', $this->service->setXpRules($user, (int) $payload['xp']));
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminModuleAccess
{
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $user = $request->user();

        if (! $user) {
            return $this->error('Unauthenticated', 401);
        }

        if ($user->hasRole('super_admin')) {
            return $next($request);
        }

        $map = [
            'contests' => ['contest_admin', 'admin'],
            'users' => ['user_moderator', 'admin'],
            'security' => ['user_moderator', 'admin'],
            'support' => ['support_admin', 'admin'],
            'content' => ['content_manager', 'admin'],
            'subscriptions' => ['contest_admin', 'admin'],
            'payments' => ['contest_admin', 'admin'],
            'reports' => ['contest_admin', 'support_admin', 'admin'],
            'leaderboard' => ['contest_admin', 'admin'],
            'notifications' => ['support_admin', 'contest_admin', 'admin'],
            'system' => ['super_admin', 'contest_admin', 'admin'],
            'roles' => ['super_admin'],
        ];

        $allowedRoles = $map[$module] ?? [];

        if ($allowedRoles === [] || ! $user->hasAnyRole($allowedRoles)) {
            return $this->error('Unauthorized access', 403);
        }

        return $next($request);
    }

    private function error(string $message, int $status): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $status);
    }
}

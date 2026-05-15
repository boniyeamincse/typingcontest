<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'country' => ['sometimes', 'string', 'size:2'],
        ]);

        $user = User::create([
            'username' => $data['username'],
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'country' => $data['country'] ?? 'US',
            'plan_type' => 'free',
            'subscription_status' => 'active',
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'User registered successfully',
            'token' => $token,
            'user' => $user->only(['id', 'username', 'name', 'email', 'country']),
        ], 201);
    }

    /**
     * Login a user
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        try {
            $token = JWTAuth::attempt($credentials);
            
            if (!$token) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            return response()->json([
                'message' => 'Login successful',
                'token' => $token,
                'user' => auth()->user()->only(['id', 'username', 'name', 'email', 'country', 'plan_type']),
            ]);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }
    }

    /**
     * Get authenticated user profile
     */
    public function me(): JsonResponse
    {
        $user = auth()->user();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'country' => $user->country,
                'plan_type' => $user->plan_type,
                'subscription_status' => $user->subscription_status,
                'subscription_end_date' => $user->subscription_end_date,
                'xp_points' => $user->xp_points,
                'global_rank' => $user->global_rank,
                'total_wpm' => $user->total_wpm,
                'accuracy_avg' => $user->accuracy_avg,
            ],
        ]);
    }

    /**
     * Logout (invalidate JWT)
     */
    public function logout(): JsonResponse
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json(['message' => 'Logout successful']);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Failed to logout'], 500);
        }
    }

    /**
     * Refresh JWT token
     */
    public function refresh(): JsonResponse
    {
        try {
            $token = JWTAuth::refresh(JWTAuth::getToken());
            return response()->json([
                'message' => 'Token refreshed',
                'token' => $token,
            ]);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not refresh token'], 500);
        }
    }
}

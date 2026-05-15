<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\SocialLoginRequest;
use App\Http\Requests\Auth\VerifyEmailRequest;
use App\Http\Resources\UserResource;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $authService)
    {
    }

    /**
     * @OA\Post(
     *   path="/api/v1/auth/register",
     *   tags={"Auth"},
     *   summary="Register user",
     *   @OA\Response(response=201, description="Registered")
     * )
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register(
            $request->validated(),
            $request->ip(),
            (string) $request->userAgent()
        );

        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
            'data' => [
                'token' => $result['token'],
                'token_type' => 'Bearer',
                'expires_at' => $result['expires_at'],
                'user' => UserResource::make($result['user']),
            ],
        ], 201);
    }

    /**
     * @OA\Post(
     *   path="/api/v1/auth/login",
     *   tags={"Auth"},
     *   summary="Login user",
     *   @OA\Response(response=200, description="Logged in")
     * )
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(
            $request->validated(),
            $request->ip(),
            (string) $request->userAgent()
        );

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'token' => $result['token'],
                'token_type' => 'Bearer',
                'expires_at' => $result['expires_at'],
                'user' => UserResource::make($result['user']),
            ],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Profile retrieved successfully',
            'data' => UserResource::make($request->user()),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        if ((bool) $request->boolean('all_devices')) {
            $this->authService->logoutAllDevices($request->user());

            return response()->json([
                'success' => true,
                'message' => 'Logged out from all devices successfully',
            ]);
        }

        $this->authService->logoutCurrentDevice($request->user());

        return response()->json([
            'success' => true,
            'message' => 'Logout successful',
        ]);
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $this->authService->sendForgotPasswordLink($request->validated('email'));

        return response()->json([
            'success' => true,
            'message' => 'Password reset link sent successfully',
        ]);
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $this->authService->resetPassword($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Password reset successful',
        ]);
    }

    public function verifyEmail(VerifyEmailRequest $request): JsonResponse
    {
        $user = $this->authService->verifyEmailToken($request->validated('token'));

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully',
            'data' => UserResource::make($user),
        ]);
    }

    public function socialGoogle(SocialLoginRequest $request): JsonResponse
    {
        $result = $this->authService->socialLogin(
            'google',
            $request->validated(),
            $request->ip(),
            (string) $request->userAgent()
        );

        return response()->json([
            'success' => true,
            'message' => 'Google login successful',
            'data' => [
                'token' => $result['token'],
                'token_type' => 'Bearer',
                'expires_at' => $result['expires_at'],
                'user' => UserResource::make($result['user']),
            ],
        ]);
    }

    public function socialGithub(SocialLoginRequest $request): JsonResponse
    {
        $result = $this->authService->socialLogin(
            'github',
            $request->validated(),
            $request->ip(),
            (string) $request->userAgent()
        );

        return response()->json([
            'success' => true,
            'message' => 'GitHub login successful',
            'data' => [
                'token' => $result['token'],
                'token_type' => 'Bearer',
                'expires_at' => $result['expires_at'],
                'user' => UserResource::make($result['user']),
            ],
        ]);
    }
}

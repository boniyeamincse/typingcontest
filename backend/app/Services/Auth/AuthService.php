<?php

namespace App\Services\Auth;

use App\Events\Auth\UserLoggedIn;
use App\Models\EmailVerificationToken;
use App\Models\User;
use App\Repositories\Auth\SocialAccountRepositoryInterface;
use App\Repositories\Auth\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class AuthService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly SocialAccountRepositoryInterface $socialAccountRepository,
        private readonly SocialProviderService $socialProviderService,
    ) {
    }

    public function register(array $payload, string $ipAddress, string $userAgent): array
    {
        return DB::transaction(function () use ($payload, $ipAddress, $userAgent) {
            $user = $this->userRepository->create([
                'name' => $payload['name'],
                'username' => $payload['username'],
                'email' => $payload['email'],
                'password' => Hash::make($payload['password']),
                'country' => strtoupper($payload['country'] ?? 'US'),
                'plan_type' => 'free',
                'subscription_status' => 'active',
            ]);

            $this->assignDefaultRole($user);

            $verificationToken = hash('sha256', Str::random(64));

            EmailVerificationToken::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'token' => hash('sha256', $verificationToken),
                    'expires_at' => now()->addHours(24),
                ]
            );

            $frontendBaseUrl = rtrim((string) env('FRONTEND_URL', 'http://localhost:3000'), '/');
            $verificationUrl = $frontendBaseUrl.'/verify-email?token='.$verificationToken;

            Mail::raw(
                "Verify your TypingContest email by visiting: {$verificationUrl}",
                static fn ($message) => $message->to($user->email)->subject('Verify your email')
            );

            $tokenResult = $this->issueToken($user, $payload['remember'] ?? false, $payload['device_name'] ?? 'registered-device');

            event(new UserLoggedIn(
                $user,
                $user->email,
                $ipAddress,
                $userAgent,
                $payload['device_name'] ?? 'registered-device'
            ));

            return [
                'token' => $tokenResult['plainTextToken'],
                'expires_at' => $tokenResult['expires_at'],
                'user' => $user->fresh(),
            ];
        });
    }

    public function login(array $payload, string $ipAddress, string $userAgent): array
    {
        $user = $this->userRepository->findByEmailOrUsername($payload['login']);

        if (! $user || ! Hash::check($payload['password'], $user->password)) {
            throw ValidationException::withMessages([
                'login' => ['Invalid credentials.'],
            ]);
        }

        if ($user->is_banned) {
            throw ValidationException::withMessages([
                'login' => ['Your account is banned.'],
            ]);
        }

        $this->userRepository->updateLastLogin($user, $ipAddress, $userAgent);

        $tokenResult = $this->issueToken($user, $payload['remember'] ?? false, $payload['device_name'] ?? 'browser-device');

        event(new UserLoggedIn(
            $user,
            $payload['login'],
            $ipAddress,
            $userAgent,
            $payload['device_name'] ?? 'browser-device'
        ));

        return [
            'token' => $tokenResult['plainTextToken'],
            'expires_at' => $tokenResult['expires_at'],
            'user' => $user->fresh(),
        ];
    }

    public function verifyEmailToken(string $token): User
    {
        $hashed = hash('sha256', $token);

        $record = EmailVerificationToken::query()
            ->where('token', $hashed)
            ->where('expires_at', '>', now())
            ->first();

        if (! $record || ! $record->user) {
            throw ValidationException::withMessages([
                'token' => ['Verification token is invalid or expired.'],
            ]);
        }

        $user = $record->user;

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        $record->delete();

        return $user->fresh();
    }

    public function sendForgotPasswordLink(string $email): void
    {
        $status = Password::sendResetLink(['email' => $email]);

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }
    }

    public function resetPassword(array $payload): void
    {
        $status = Password::reset(
            [
                'email' => $payload['email'],
                'password' => $payload['password'],
                'password_confirmation' => $payload['password_confirmation'],
                'token' => $payload['token'],
            ],
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                // Invalidate all previous API tokens after password reset.
                $user->tokens()->delete();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }
    }

    public function socialLogin(string $provider, array $payload, string $ipAddress, string $userAgent): array
    {
        $providerData = match ($provider) {
            'google' => $this->socialProviderService->fetchGoogleUser($payload['access_token']),
            'github' => $this->socialProviderService->fetchGithubUser($payload['access_token']),
            default => throw ValidationException::withMessages([
                'provider' => ['Unsupported social provider.'],
            ]),
        };

        if (empty($providerData['provider_user_id'])) {
            throw ValidationException::withMessages([
                'access_token' => ['Unable to read provider user id.'],
            ]);
        }

        if (empty($providerData['email'])) {
            throw ValidationException::withMessages([
                'access_token' => ['The provider did not return a verified email address.'],
            ]);
        }

        $socialAccount = $this->socialAccountRepository->findByProviderAndProviderId($provider, $providerData['provider_user_id']);

        if ($socialAccount) {
            $user = $socialAccount->user;
        } else {
            $user = $this->userRepository->findByEmail($providerData['email']);

            if (! $user) {
                $baseUsername = Str::slug($providerData['name'] ?: Str::before($providerData['email'], '@'), '_');
                $username = $baseUsername;
                $counter = 1;

                while ($this->userRepository->findByEmailOrUsername($username)) {
                    $username = $baseUsername.'_'.$counter++;
                }

                $user = $this->userRepository->create([
                    'name' => $providerData['name'] ?? $username,
                    'username' => $username,
                    'email' => $providerData['email'],
                    'email_verified_at' => now(),
                    'password' => Hash::make(Str::random(32)),
                    'country' => 'US',
                    'plan_type' => 'free',
                    'subscription_status' => 'active',
                ]);

                $this->assignDefaultRole($user);
            }

            $this->socialAccountRepository->updateOrCreateForUser($user, $provider, [
                'provider_user_id' => $providerData['provider_user_id'],
                'provider_email' => $providerData['email'],
                'provider_avatar' => $providerData['avatar'],
                'access_token' => $payload['access_token'],
            ]);
        }

        $this->userRepository->updateLastLogin($user, $ipAddress, $userAgent);

        $tokenResult = $this->issueToken($user, $payload['remember'] ?? false, $payload['device_name'] ?? 'social-device');

        event(new UserLoggedIn(
            $user,
            $providerData['email'],
            $ipAddress,
            $userAgent,
            $payload['device_name'] ?? 'social-device'
        ));

        return [
            'token' => $tokenResult['plainTextToken'],
            'expires_at' => $tokenResult['expires_at'],
            'user' => $user->fresh(),
        ];
    }

    public function logoutCurrentDevice(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }

    public function logoutAllDevices(User $user): void
    {
        $user->tokens()->delete();
    }

    private function issueToken(User $user, bool $remember = false, string $deviceName = 'api-device'): array
    {
        $expiresAt = $remember
            ? now()->addDays(30)
            : now()->addMinutes((int) config('sanctum.expiration', 120));

        $token = $user->createToken($deviceName, ['*'], $expiresAt);

        return [
            'plainTextToken' => $token->plainTextToken,
            'expires_at' => $expiresAt,
        ];
    }

    private function assignDefaultRole(User $user): void
    {
        Role::findOrCreate('free_user', 'api');
        $user->assignRole('free_user');
    }
}

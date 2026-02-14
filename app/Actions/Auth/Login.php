<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Data\Requests\LoginRequest;
use App\Data\Response\MessageResponse;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * IMPORTANT: This action establishes session-based authentication.
 *
 * This action only:
 * 1. Validates credentials
 * 2. Establishes a Laravel session
 * 3. Returns success status
 *
 * The frontend stores user data in localStorage and uses session cookies for API auth.
 *
 */
class Login
{
    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    private function authenticate(
        string $email,
        string $password,
        bool $remember,
    ): void {
        $this->ensureIsNotRateLimited($email);

        if (!Auth::attempt([
            'email' => $email,
            'password' => $password,
        ], $remember)) {
            RateLimiter::hit($this->throttleKey($email));
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    private function ensureIsNotRateLimited(string $email): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey($email), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey($email));

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    private function throttleKey(string $email): string
    {
        return Str::transliterate(Str::lower($email) . '|' . request()->ip());
    }

    /**
     * Authenticate user and establish session.
     *
     * After session authentication, the frontend stores user data and uses
     * session cookies for subsequent API requests.
     */
    public function __invoke(LoginRequest $loginData): JsonResponse
    {
        $guestUser = Auth::user();

        $this->authenticate(
            $loginData->email,
            $loginData->password,
            $loginData->remember,
        );

        if (request()->hasSession()) {
            request()->session()->regenerate();
            request()->session()->forget('guest_user_id');

            // Clear stale password hash stored by Sanctum's AuthenticateSession
            // middleware when the guest user (null password) was authenticated.
            // Without this, AuthenticateSession sees a hash mismatch between the
            // guest's null hash and the real user's bcrypt hash, causing a 401.
            /** @var string $guard */
            foreach (\Illuminate\Support\Arr::wrap(config('sanctum.guard', 'web')) as $guard) {
                request()->session()->forget('password_hash_' . $guard);
            }
        }

        // Delete the guest user now that the real user is authenticated
        if ($guestUser instanceof User && $guestUser->is_guest) {
            $guestUser->delete();
        }

        RateLimiter::clear($this->throttleKey($loginData->email));

        return response()->json(MessageResponse::from([
            'message' => 'Authentication successful',
        ]));
    }
}

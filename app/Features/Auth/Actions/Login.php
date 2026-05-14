<?php

declare(strict_types=1);

namespace App\Features\Auth\Actions;

use App\Features\Auth\Requests\LoginRequest;
use App\Features\Auth\Responses\MessageResponse;
use App\Models\User;
use App\Services\GuestUserMergeService;
use App\Support\SanctumSessionPasswordVerifier;
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
 * When the session user is a server-side guest, a successful login merges that guest
 * into the authenticated account via {@see \App\Services\GuestUserMergeService}.
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
        $guestId = null;
        $maybeGuest = Auth::user();
        if ($maybeGuest instanceof User && $maybeGuest->is_guest) {
            $guestId = $maybeGuest->id;
        }

        $this->authenticate(
            $loginData->email,
            $loginData->password,
            $loginData->remember,
        );

        $authenticated = Auth::user();
        if (
            $guestId !== null
            && $authenticated instanceof User
            && (string) $authenticated->id !== (string) $guestId
        ) {
            $guest = User::query()->find($guestId);
            if ($guest instanceof User && $guest->is_guest) {
                app(GuestUserMergeService::class)->mergeGuestIntoUser(
                    $guest,
                    $authenticated,
                );
            }
        }

        $request = request();
        if ($request->hasSession()) {
            $request->session()->regenerate();
            SanctumSessionPasswordVerifier::forgetCachedPasswordHashes(
                $request,
            );
        }

        RateLimiter::clear($this->throttleKey($loginData->email));

        return response()->json(MessageResponse::from([
            'message' => 'Authentication successful',
        ]));
    }
}

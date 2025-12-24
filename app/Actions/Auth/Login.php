<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Data\Requests\LoginRequest;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class Login
{
    /**
     * Handle an incoming authentication request.
     */
    public function __invoke(LoginRequest $loginData, Request $request): Response
    {
        $this->authenticate($loginData->email, $loginData->password, $loginData->remember);
        $request->session()->regenerate();

        $intendedUrl = $request->session()->pull('url.intended');
        if (! is_string($intendedUrl)) {
            $intendedUrl = route('home', absolute: false);
        }

        return Inertia::location($intendedUrl);
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    private function authenticate(string $email, string $password, bool $remember): void
    {
        $this->ensureIsNotRateLimited($email);

        if (! Auth::attempt(['email' => $email, 'password' => $password], $remember)) {
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
        if (! RateLimiter::tooManyAttempts($this->throttleKey($email), 5)) {
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
}

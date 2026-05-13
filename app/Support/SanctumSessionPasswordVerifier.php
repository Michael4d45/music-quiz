<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;

/**
 * Sanctum's {@see \Laravel\Sanctum\Http\Middleware\AuthenticateSession} stores a
 * copy of the authenticated user's password hash in the session. After login or
 * registration (especially guest → registered upgrade), session regeneration
 * keeps that value while the user's credentials changed, so the next SPA call to
 * stateful API routes fails with {@see \Illuminate\Auth\AuthenticationException}.
 *
 * Clear the cached hashes after auth so the middleware can re-seed them on the following request.
 */
final class SanctumSessionPasswordVerifier
{
    public static function forgetCachedPasswordHashes(Request $request): void
    {
        if (!$request->hasSession()) {
            return;
        }

        foreach (Arr::wrap(config('sanctum.guard', 'web')) as $guard) {
            if (!is_string($guard)) {
                continue;
            }

            $request->session()->forget('password_hash_' . $guard);
        }
    }
}

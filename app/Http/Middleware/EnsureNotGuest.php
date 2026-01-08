<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureNotGuest
{
    /**
     * Handle an incoming request.
     *
     * Ensure the authenticated user is not a guest user.
     * Guest users don't need email verification.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->is_guest) {
            return redirect()->route('home');
        }

        return $next($request);
    }
}

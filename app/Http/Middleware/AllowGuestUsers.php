<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AllowGuestUsers
{
    /**
     * Handle an incoming request.
     *
     * Allow access if user is not authenticated or is authenticated as a guest user.
     * This replaces the standard 'guest' middleware in our guest user system.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Allow if not authenticated or if authenticated as guest
        if (!$user || $user->is_guest) {
            return $next($request);
        }

        // Redirect authenticated non-guest users away from login/register
        return redirect()->route('home');
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRegisteredUser
{
    /**
     * Require a persisted session user that is not a server-side guest placeholder.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user instanceof User || $user->is_guest) {
            abort(403, 'A registered account is required.');
        }

        return $next($request);
    }
}

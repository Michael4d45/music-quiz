<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureGuestUser
{
    /**
     * When the request is not already authenticated, create a guest user if needed,
     * persist their id in the session, and log them in on the web guard so session
     * API routes can resolve request()->user() (guest or registered).
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            return $next($request);
        }

        if (!session()->has('guest_user_id')) {
            $user = User::create([
                'is_guest' => true,
            ]);

            session(['guest_user_id' => $user->id]);
        }

        Auth::loginUsingId(session('guest_user_id'));

        return $next($request);
    }
}

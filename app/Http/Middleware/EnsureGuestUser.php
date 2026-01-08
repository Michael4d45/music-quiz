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
     * Handle an incoming request.
     *
     * Create a guest user on first meaningful interaction (POST requests or specific pages).
     * This ensures auth()->user() exists when users start interacting with features.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Don't create guest users on initial home page visits
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

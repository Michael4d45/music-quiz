<?php

declare(strict_types=1);

namespace App\Features\Auth\Actions;

use App\Features\Auth\Requests\RegisterRequest;
use App\Features\Auth\Responses\MessageResponse;
use App\Models\User;
use App\Support\SanctumSessionPasswordVerifier;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * IMPORTANT: This action establishes session-based authentication.
 *
 * After registration, the frontend stores user data and uses
 * session cookies for subsequent API requests.
 *
 * When the browser already has a guest session user, this upgrades that row
 * in place instead of creating a second account.
 */
class Register
{
    /**
     * Handle an incoming registration request.
     *
     * Creates user, logs them in with session, then returns success.
     * Frontend stores user data and uses session cookies for API auth.
     */
    public function __invoke(RegisterRequest $registerData): JsonResponse
    {
        $current = Auth::user();

        if ($current instanceof User && $current->is_guest) {
            $current->update([
                'name' => $registerData->name,
                'email' => $registerData->email,
                'password' => Hash::make($registerData->password),
                'email_verified_at' => null,
                'is_admin' => false,
                'is_guest' => false,
                'google_id' => null,
            ]);

            event(new Registered($current));

            Auth::login($current);

            $request = request();
            if ($request->hasSession()) {
                $request->session()->regenerate();
                SanctumSessionPasswordVerifier::forgetCachedPasswordHashes(
                    $request,
                );
            }

            return response()->json(MessageResponse::from([
                'message' => 'Registration successful',
            ]));
        }

        $user = User::create([
            'name' => $registerData->name,
            'email' => $registerData->email,
            'password' => Hash::make($registerData->password),
            'email_verified_at' => null,
            'is_admin' => false,
            'is_guest' => false,
            'google_id' => null,
        ]);

        event(new Registered($user));

        Auth::login($user);

        $request = request();
        if ($request->hasSession()) {
            $request->session()->regenerate();
            SanctumSessionPasswordVerifier::forgetCachedPasswordHashes(
                $request,
            );
        }

        return response()->json(MessageResponse::from([
            'message' => 'Registration successful',
        ]));
    }
}

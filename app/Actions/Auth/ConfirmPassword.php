<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ConfirmPassword
{
    /**
     * Confirm the user's password.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $user = $request->user();
        assert($user instanceof User);

        if (! Auth::guard('web')->validate([
            'email' => $user->email,
            'password' => $request->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        $request->session()->put('auth.password_confirmed_at', time());

        return redirect()->intended(route('home', absolute: false));
    }
}

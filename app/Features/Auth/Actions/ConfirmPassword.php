<?php

declare(strict_types=1);

namespace App\Features\Auth\Actions;

use App\Features\Auth\Requests\ConfirmPasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ConfirmPassword
{
    /**
     * Confirm the user's password.
     */
    public function __invoke(ConfirmPasswordRequest $data): RedirectResponse
    {
        $user = assertedUser();

        if (!Auth::guard('web')->validate([
            'email' => $user->email,
            'password' => $data->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        request()->session()->put('auth.password_confirmed_at', time());

        return redirect()->intended(route('home', absolute: false));
    }
}

<?php

declare(strict_types=1);

namespace App\Features\Auth\Actions;

use App\Features\Auth\Requests\GoogleOAuthRedirectRequest;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse;

class RedirectToGoogle
{
    /**
     * Redirect the user to Google OAuth.
     */
    public function __invoke(GoogleOAuthRedirectRequest $data): RedirectResponse
    {
        $remember = $data->remember ?? false;
        request()->session()->put('auth.google.remember', $remember);

        /** @var \Laravel\Socialite\Two\GoogleProvider $driver */
        $driver = Socialite::driver('google');

        $driver->scopes([
            'openid',
            'profile',
            'email',
        ]);

        return $driver->redirect();
    }
}

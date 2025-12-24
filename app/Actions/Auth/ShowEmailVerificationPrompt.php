<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShowEmailVerificationPrompt
{
    /**
     * Display the email verification prompt.
     */
    public function __invoke(Request $request): RedirectResponse|Response
    {
        $user = $request->user();
        assert($user instanceof User);

        return $user->hasVerifiedEmail()
            ? redirect()->intended(route('home', absolute: false))
            : Inertia::render('Auth/VerifyEmail');
    }
}

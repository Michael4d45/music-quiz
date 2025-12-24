<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SendEmailVerificationNotification
{
    /**
     * Send a new email verification notification.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $user = $request->user();
        assert($user instanceof User);

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(route('home', absolute: false));
        }

        $user->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}

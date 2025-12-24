<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShowResetPassword
{
    /**
     * Display the password reset view.
     */
    public function __invoke(Request $request): Response
    {
        return Inertia::render('Auth/ResetPassword', [
            'email' => (string) $request->query('email', ''),
            'token' => (string) $request->route('token'),
        ]);
    }
}

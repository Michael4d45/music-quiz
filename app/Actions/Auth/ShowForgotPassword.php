<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use Inertia\Inertia;
use Inertia\Response;

class ShowForgotPassword
{
    /**
     * Display the password reset link request view.
     */
    public function __invoke(): Response
    {
        return Inertia::render('Auth/ForgotPassword');
    }
}

<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use Inertia\Inertia;
use Inertia\Response;

class ShowConfirmPassword
{
    /**
     * Show the confirm password view.
     */
    public function __invoke(): Response
    {
        return Inertia::render('Auth/ConfirmPassword');
    }
}

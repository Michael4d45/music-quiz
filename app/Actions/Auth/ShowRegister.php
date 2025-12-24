<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ShowRegister
{
    /**
     * Display the registration view.
     */
    public function __invoke(): InertiaResponse
    {
        return Inertia::render('auth/register');
    }
}

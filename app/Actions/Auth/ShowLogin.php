<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ShowLogin
{
    /**
     * Display the login view.
     */
    public function __invoke(): InertiaResponse
    {
        return Inertia::render('auth/login', [
            'status' => session('status'),
        ]);
    }
}

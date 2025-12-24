<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class Logout
{
    /**
     * Destroy an authenticated session.
     */
    public function __invoke(Request $request): Response
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Inertia::location(route('home'));
    }
}

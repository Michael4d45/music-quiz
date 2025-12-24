<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class Register
{
    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function __invoke(Request $request): Response
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => (string) $request->string('name'),
            'email' => (string) $request->string('email'),
            'password' => Hash::make((string) $request->string('password')),
            'is_admin' => false,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return Inertia::location(route('home'));
    }
}

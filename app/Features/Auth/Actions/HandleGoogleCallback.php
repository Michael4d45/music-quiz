<?php

declare(strict_types=1);

namespace App\Features\Auth\Actions;

use App\Models\User;
use App\Services\GuestUserMergeService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse;

class HandleGoogleCallback
{
    public function __invoke(): RedirectResponse
    {
        /** @var User|null $currentUser */
        $currentUser = Auth::user();
        $hadRegisteredSession =
            $currentUser instanceof User && !$currentUser->is_guest;
        $wasAuthenticated = $currentUser instanceof User;

        try {
            /** @var \Laravel\Socialite\Two\GoogleProvider $driver */
            $driver = Socialite::driver('google');

            $googleUser = $driver->user();
            $googleId = $googleUser->getId();
            $email = $googleUser->getEmail();

            if (!$email) {
                return $this->rejectMissingEmail($hadRegisteredSession);
            }

            $user = $this->resolveUser(
                googleId: $googleId,
                email: $email,
                googleUser: $googleUser,
                currentUser: $currentUser,
                wasAuthenticated: $wasAuthenticated,
            );

            if ($user instanceof RedirectResponse) {
                return $user;
            }

            $remember = session()->pull('auth.google.remember', false);
            if (!is_bool($remember)) {
                $remember = false;
            }
            Auth::login($user, $remember);

            return redirect(
                ($hadRegisteredSession ? '/profile' : '/') . '?auth=success',
            );
        } catch (\Throwable $e) {
            Log::error('Google Error', [
                'error' => $e->getMessage(),
            ]);

            $redirectPath = $hadRegisteredSession ? '/profile' : '/login';

            return redirect($redirectPath);
        }
    }

    /**
     * @return User|RedirectResponse
     */
    private function resolveUser(
        string $googleId,
        string $email,
        SocialiteUser $googleUser,
        null|User $currentUser,
        bool $wasAuthenticated,
    ): User|RedirectResponse {
        $googleAccountUser = User::query()
            ->where('google_id', $googleId)
            ->first();

        if ($googleAccountUser instanceof User) {
            if (
                $wasAuthenticated
                && $currentUser instanceof User
                && $currentUser->id !== $googleAccountUser->id
                && $currentUser->is_guest
            ) {
                app(GuestUserMergeService::class)->mergeGuestIntoUser(
                    $currentUser,
                    $googleAccountUser,
                );

                return $googleAccountUser;
            }

            if (
                $wasAuthenticated
                && $currentUser instanceof User
                && $currentUser->id !== $googleAccountUser->id
                && !$currentUser->is_guest
            ) {
                return $this->rejectGoogleAlreadyLinked();
            }

            return $googleAccountUser;
        }

        $emailUser = User::query()->where('email', $email)->first();

        if ($emailUser instanceof User) {
            if (
                $wasAuthenticated
                && $currentUser instanceof User
                && $currentUser->is_guest
                && $currentUser->id !== $emailUser->id
            ) {
                app(GuestUserMergeService::class)->mergeGuestIntoUser(
                    $currentUser,
                    $emailUser,
                );
            } elseif (
                $wasAuthenticated
                && $currentUser instanceof User
                && !$currentUser->is_guest
                && $currentUser->id !== $emailUser->id
            ) {
                return $this->rejectEmailMismatch();
            }

            $name = $googleUser->getName() ?? $emailUser->name ?? 'User';
            $emailUser->update([
                'name' => Str::limit($name, 255),
                'google_id' => $googleId,
                'verified_google_email' => $email,
                'email_verified_at' => $emailUser->email_verified_at ?? now(),
            ]);

            return $emailUser;
        }

        if ($wasAuthenticated && $currentUser instanceof User) {
            $emailToUse = $currentUser->is_guest
                ? $email
                : $currentUser->email ?? $email;

            $name = $googleUser->getName() ?? $currentUser->name ?? 'User';
            $currentUser->update([
                'name' => Str::limit($name, 255),
                'email' => $emailToUse,
                'google_id' => $googleId,
                'verified_google_email' => $email,
                'email_verified_at' =>
                    $currentUser->email_verified_at
                        ?? ($emailToUse === $email ? now() : null),
                'is_guest' => false,
            ]);

            return $currentUser;
        }

        $name = $googleUser->getName();
        assert(is_string($name), 'User name must be a string');

        return User::query()->create([
            'name' => Str::limit($name, 255),
            'email' => $email,
            'password' => Hash::make(Str::random(32)),
            'google_id' => $googleId,
            'verified_google_email' => $email,
            'email_verified_at' => now(),
            'is_guest' => false,
            'is_admin' => false,
        ]);
    }

    private function rejectMissingEmail(bool $hadRegisteredSession): RedirectResponse
    {
        $redirectPath = $hadRegisteredSession ? '/profile' : '/login';
        $message = 'Google did not provide an email address. Please grant email access and try again.';

        return redirect(
            $redirectPath . '?auth=error&message=' . urlencode($message),
        );
    }

    private function rejectGoogleAlreadyLinked(): RedirectResponse
    {
        $message = 'This Google account is already connected to another user.';

        return redirect('/profile?auth=error&message=' . urlencode($message));
    }

    private function rejectEmailMismatch(): RedirectResponse
    {
        $message = 'A user with this email already exists. Please use the same email account.';

        return redirect('/profile?auth=error&message=' . urlencode($message));
    }
}

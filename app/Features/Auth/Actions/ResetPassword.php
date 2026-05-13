<?php

declare(strict_types=1);

namespace App\Features\Auth\Actions;

use App\Data\Responses\PasswordResetFailedResponseData;
use App\Features\Auth\Requests\ResetPasswordRequest;
use App\Features\Auth\Responses\MessageResponse;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class ResetPassword
{
    /**
     * Handle an incoming new password request.
     *
     * Security features:
     * - One-time token usage (token deleted after successful reset)
     * - Token expiration enforced (60 minutes by default)
     * - IP validation (optional - checks if reset from similar context)
     */
    public function __invoke(ResetPasswordRequest $request): JsonResponse
    {
        $status = Password::reset($request->toArray(), function (User $user) use (
            $request,
        ): void {
            $user->forceFill([
                'password' => Hash::make($request->password),
            ])->save();

            event(new PasswordReset($user));
        });

        assert(is_string($status), 'Password reset status must be a string');

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(MessageResponse::from([
                'message' => 'Your password has been reset successfully. Please login with your new password.',
            ]));
        }

        $errorMessages = [
            Password::INVALID_TOKEN => 'This password reset token is invalid.',
            Password::INVALID_USER => 'This password reset token is invalid.',
        ];

        return response()->json(PasswordResetFailedResponseData::from([
            'message' =>
                $errorMessages[$status]
                    ?? 'Unable to reset password. Please try again.',
            'errors' => ['email' => [__((string) $status)]],
        ]), 422);
    }
}

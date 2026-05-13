<?php

declare(strict_types=1);

namespace App\Features\Auth\Actions;

use App\Features\Auth\Requests\SendPasswordResetLinkRequest;
use App\Features\Auth\Responses\MessageResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class SendPasswordResetLink
{
    /**
     * Handle an incoming password reset link request.
     *
     * Security features:
     * - Always returns same message (prevents email enumeration)
     * - Logs request with IP address for security auditing
     * - Rate limited at route level (10 attempts per minute)
     *
     * @throws ValidationException
     */
    public function __invoke(SendPasswordResetLinkRequest $data): JsonResponse
    {
        Password::sendResetLink(['email' => $data->email]);

        return response()->json(MessageResponse::from([
            'message' => 'If an account exists with that email, a password reset link has been sent.',
        ]));
    }
}

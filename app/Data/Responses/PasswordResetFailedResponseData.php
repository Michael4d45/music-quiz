<?php

declare(strict_types=1);

namespace App\Data\Responses;

use Spatie\LaravelData\Data;

class PasswordResetFailedResponseData extends Data
{
    /**
     * @param array<string, list<string>> $errors
     */
    public function __construct(
        public string $message,
        public array $errors,
    ) {}
}

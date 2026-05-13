<?php

declare(strict_types=1);

namespace App\Data\Responses;

use Spatie\LaravelData\Data;

class ErrorResponseData extends Data
{
    public function __construct(
        public string $error,
    ) {}
}

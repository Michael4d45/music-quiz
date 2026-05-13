<?php

declare(strict_types=1);

namespace App\Features\Auth\Requests;

use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Attributes\Validation\Sometimes;
use Spatie\LaravelData\Data;

class GoogleOAuthRedirectRequest extends Data
{
    public function __construct(
        #[Sometimes, BooleanType]
        public null|bool $remember = null,
    ) {}
}

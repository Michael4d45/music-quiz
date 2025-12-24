<?php

declare(strict_types=1);

namespace App\Data\Props;

use Illuminate\Http\Request;
use Spatie\LaravelData\Data;

class SharedProps extends Data
{
    public function __construct(
        public AuthProps $auth,
        public FlashProps $flash,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            auth: AuthProps::fromRequest($request),
            flash: FlashProps::fromRequest($request),
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Data\Props;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\OnceProp;
use Spatie\LaravelData\Data;

class SharedProps extends Data
{
    public function __construct(
        public AuthProps|OnceProp $auth,
        public FlashProps $flash,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            auth: Inertia::once(fn () => AuthProps::fromRequest($request)),
            flash: FlashProps::fromRequest($request),
        );
    }
}

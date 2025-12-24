<?php

declare(strict_types=1);

namespace App\Data\Props;

use Illuminate\Http\Request;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

class FlashProps extends Data
{
    public function __construct(
        public bool|Lazy|string|null $success = null,
        public Lazy|string|null $error = null,
        public Lazy|string|null $info = null,
        public Lazy|string|null $warning = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            success: Lazy::closure(fn () => $request->session()->get('success')),
            error: Lazy::closure(fn () => $request->session()->get('error')),
            info: Lazy::closure(fn () => $request->session()->get('info')),
            warning: Lazy::closure(fn () => $request->session()->get('warning')),
        );
    }
}

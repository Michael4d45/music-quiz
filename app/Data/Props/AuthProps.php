<?php

declare(strict_types=1);

namespace App\Data\Props;

use App\Data\Models\UserData;
use Illuminate\Http\Request;
use Spatie\LaravelData\Data;

class AuthProps extends Data
{
    public function __construct(
        public UserData|null $user,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            user: $request->user() ? UserData::from($request->user()) : null,
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Data\Props;

use App\Data\Models\UserData;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\LaravelData\Data;

class AuthProps extends Data
{
    public function __construct(
        public UserData|null $user,
        public UserData|null $impersonator,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $impersonator = null;
        $impersonatorId = $request->session()->get('impersonator_id');

        if ($impersonatorId) {
            $impersonatorUser = User::find($impersonatorId);
            if ($impersonatorUser) {
                $impersonator = UserData::from($impersonatorUser);
            }
        }

        return new self(
            user: $request->user() ? UserData::from($request->user()) : null,
            impersonator: $impersonator,
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Data\Models;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\AutoWhenLoadedLazy;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

class UserData extends Data
{
    public function __construct(
        public string $id,
        public string $name,
        public bool $is_admin,
        public string $email,
        public Carbon|null $email_verified_at,
        public Carbon|null $created_at,
        public Carbon|null $updated_at,
        /** @var Collection<array-key,GameSessionData> $game_sessions */
        #[AutoWhenLoadedLazy('gameSessions')]
        public Collection|Lazy $game_sessions,
        /** @var Collection<array-key,SessionParticipantData> $participants */
        #[AutoWhenLoadedLazy]
        public Collection|Lazy $participants,
        /** @var Collection<array-key,UserStatisticData> $statistics */
        #[AutoWhenLoadedLazy]
        public Collection|Lazy $statistics,
    ) {}
}

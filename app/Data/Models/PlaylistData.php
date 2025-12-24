<?php

declare(strict_types=1);

namespace App\Data\Models;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\AutoWhenLoadedLazy;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

class PlaylistData extends Data
{
    public function __construct(
        public string $id,
        public string $user_id,
        public string $name,
        public string|null $description,
        public bool $is_public,
        public int $play_count,
        public Carbon|null $created_at,
        public Carbon|null $updated_at,
        /** @var UserData $user */
        #[AutoWhenLoadedLazy]
        public UserData|Lazy $user,
        /** @var Collection<array-key,PlaylistItemData> $items */
        #[AutoWhenLoadedLazy]
        public Collection|Lazy $items,
        /** @var Collection<array-key,GameSessionData> $game_sessions */
        #[AutoWhenLoadedLazy("gameSessions")]
        public Collection|Lazy $game_sessions,
    ) {}
}

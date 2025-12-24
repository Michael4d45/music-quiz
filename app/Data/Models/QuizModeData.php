<?php

declare(strict_types=1);

namespace App\Data\Models;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\AutoWhenLoadedLazy;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

class QuizModeData extends Data
{
    public function __construct(
        public string $id,
        public string $name,
        public string|null $description,
        public bool $allows_host_override,
        public bool $requires_manual_scoring,
        public Carbon|null $created_at,
        public Carbon|null $updated_at,
        /** @var Collection<array-key,GameSessionData> $game_sessions */
        #[AutoWhenLoadedLazy('gameSessions')]
        public Collection|Lazy $game_sessions,
    ) {}
}

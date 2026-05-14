<?php

declare(strict_types=1);

namespace App\Data\Responses;

use App\Data\Models\GameSessionData;
use Spatie\LaravelData\Data;

class GameSessionRoomViewData extends Data
{
    /**
     * @param list<SessionRoundGameplayData> $rounds
     */
    public function __construct(
        public GameSessionData $session,
        public array $rounds,
        public bool $viewer_is_host,
        public null|string $viewer_participant_id,
    ) {}
}

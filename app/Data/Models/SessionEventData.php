<?php

declare(strict_types=1);

namespace App\Data\Models;

use App\Enums\EventType;
use Illuminate\Support\Carbon;
use Spatie\LaravelData\Attributes\AutoWhenLoadedLazy;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

class SessionEventData extends Data
{
    public function __construct(
        public string $id,
        public string $session_id,
        public EventType|null $event_type,
        public string|null $participant_id,
        /** @var array<string, mixed>|null $payload */
        public array|null $payload,
        public Carbon|null $created_at,
        public Carbon|null $updated_at,
        /** @var GameSessionData $session */
        #[AutoWhenLoadedLazy]
        public GameSessionData|Lazy $session,
        /** @var SessionParticipantData|null $participant */
        #[AutoWhenLoadedLazy]
        public SessionParticipantData|Lazy|null $participant,
    ) {}
}

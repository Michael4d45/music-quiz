<?php

declare(strict_types=1);

namespace App\Data\Models;

use App\Enums\EventType;
use Illuminate\Support\Carbon;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class SessionEventData extends Data
{
    public function __construct(
        public string $id,
        public string $session_id,
        public null|EventType $event_type,
        public null|string $participant_id,
        /** @var array<string, mixed>|null $payload */
        public null|array $payload,
        public null|Carbon $created_at,
        public null|Carbon $updated_at,
        /** @var GameSessionData|Optional $session */
        public GameSessionData|Optional $session,
        /** @var SessionParticipantData|null|Optional $participant */
        public Optional|SessionParticipantData|null $participant,
    ) {}
}

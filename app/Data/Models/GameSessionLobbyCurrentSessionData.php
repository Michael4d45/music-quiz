<?php

declare(strict_types=1);

namespace App\Data\Models;

use App\Models\GameSession;
use Spatie\LaravelData\Data;

class GameSessionLobbyCurrentSessionData extends Data
{
    public function __construct(
        public string $id,
        public string $room_code,
        public string $host_id,
        public string $host_display_name,
        public string $quiz_mode_name,
        public null|string $playlist_name,
        public int $max_players,
        public int $participant_count,
        public string $status,
        public bool $is_public,
    ) {}

    public static function fromGameSession(GameSession $session): self
    {
        $host = $session->host;
        $hostLabel = $host->name ?? 'Host';
        if ($hostLabel === '') {
            $hostLabel = 'Host';
        }

        $participantCount = $session->relationLoaded('participants')
            ? $session->participants->count()
            : (int) $session->participants_count;

        return self::from([
            'id' => $session->id,
            'room_code' => $session->room_code,
            'host_id' => $session->host_id,
            'host_display_name' => $hostLabel,
            'quiz_mode_name' => $session->quizMode->name,
            'playlist_name' => $session->playlist?->name,
            'max_players' => $session->max_players,
            'participant_count' => $participantCount,
            'status' => $session->status->value,
            'is_public' => $session->is_public,
        ]);
    }
}

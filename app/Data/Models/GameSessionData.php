<?php

declare(strict_types=1);

namespace App\Data\Models;

use App\Enums\SessionStatus;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class GameSessionData extends Data
{
    public function __construct(
        public string $id,
        public string $host_id,
        public string $room_code,
        public SessionStatus $status,
        public string $quiz_mode_id,
        public string $scoring_rule_id,
        public null|string $playlist_id,
        public int $max_players,
        public null|Carbon $started_at,
        public null|Carbon $ended_at,
        public null|Carbon $created_at,
        public null|Carbon $updated_at,
        /** @var UserData|Optional $host */
        public Optional|UserData $host,
        /** @var QuizModeData|Optional $quiz_mode */
        public Optional|QuizModeData $quiz_mode,
        /** @var ScoringRuleData|Optional $scoring_rule */
        public Optional|ScoringRuleData $scoring_rule,
        /** @var PlaylistData|null|Optional $playlist */
        public Optional|PlaylistData|null $playlist,
        /** @var Collection<array-key,SessionParticipantData>|Optional */
        public Collection|Optional $participants,
        /** @var Collection<array-key,SessionRoundData>|Optional */
        public Collection|Optional $rounds,
        /** @var Collection<array-key,SessionEventData>|Optional */
        public Collection|Optional $events,
        /** @var Collection<array-key,SessionFinalScoreData>|Optional */
        public Collection|Optional $final_scores,
    ) {}
}

<?php

declare(strict_types=1);

namespace App\Data\Models;

use App\Enums\SessionStatus;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\AutoWhenLoadedLazy;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

class GameSessionData extends Data
{
    public function __construct(
        public string $id,
        public string $host_id,
        public string $room_code,
        public SessionStatus $status,
        public string $quiz_mode_id,
        public string $scoring_rule_id,
        public string|null $playlist_id,
        public int $max_players,
        public Carbon|null $started_at,
        public Carbon|null $ended_at,
        public Carbon|null $created_at,
        public Carbon|null $updated_at,
        /** @var UserData $host */
        #[AutoWhenLoadedLazy]
        public Lazy|UserData $host,
        /** @var QuizModeData $quiz_mode */
        #[AutoWhenLoadedLazy('quizMode')]
        public Lazy|QuizModeData $quiz_mode,
        /** @var ScoringRuleData $scoring_rule */
        #[AutoWhenLoadedLazy('scoringRule')]
        public Lazy|ScoringRuleData $scoring_rule,
        /** @var PlaylistData|null $playlist */
        #[AutoWhenLoadedLazy]
        public Lazy|PlaylistData|null $playlist,
        /** @var Collection<array-key,SessionParticipantData> $participants */
        #[AutoWhenLoadedLazy]
        public Collection|Lazy $participants,
        /** @var Collection<array-key,SessionRoundData> $rounds */
        #[AutoWhenLoadedLazy]
        public Collection|Lazy $rounds,
        /** @var Collection<array-key,SessionEventData> $events */
        #[AutoWhenLoadedLazy]
        public Collection|Lazy $events,
        /** @var Collection<array-key,SessionFinalScoreData> $final_scores */
        #[AutoWhenLoadedLazy('finalScores')]
        public Collection|Lazy $final_scores,
    ) {}
}

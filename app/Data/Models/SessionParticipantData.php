<?php

declare(strict_types=1);

namespace App\Data\Models;

use App\Enums\Role;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\AutoWhenLoadedLazy;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

class SessionParticipantData extends Data
{
    public function __construct(
        public string $id,
        public string $session_id,
        public string|null $user_id,
        public string|null $guest_name,
        public Role $role,
        public int $current_total_score,
        public bool $is_connected,
        public Carbon $joined_at,
        public Carbon|null $buzzed_in_at,
        /** @var GameSessionData $session */
        #[AutoWhenLoadedLazy]
        public GameSessionData|Lazy $session,
        /** @var UserData|null $user */
        #[AutoWhenLoadedLazy]
        public Lazy|UserData|null $user,
        /** @var Collection<array-key,PlayerAnswerData> $answers */
        #[AutoWhenLoadedLazy]
        public Collection|Lazy $answers,
        /** @var SessionFinalScoreData|null $final_score */
        #[AutoWhenLoadedLazy('finalScore')]
        public Lazy|SessionFinalScoreData|null $final_score,
    ) {}
}

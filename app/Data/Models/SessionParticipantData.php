<?php

declare(strict_types=1);

namespace App\Data\Models;

use App\Enums\Role;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class SessionParticipantData extends Data
{
    public function __construct(
        public string $id,
        public string $session_id,
        public null|string $user_id,
        public null|string $guest_name,
        public Role $role,
        public int $current_total_score,
        public bool $is_connected,
        public Carbon $joined_at,
        public null|Carbon $buzzed_in_at,
        /** @var GameSessionData|Optional $session */
        public GameSessionData|Optional $session,
        /** @var UserData|null|Optional $user */
        public Optional|UserData|null $user,
        /** @var Collection<array-key,PlayerAnswerData>|Optional */
        public Collection|Optional $answers,
        /** @var SessionFinalScoreData|null|Optional $final_score */
        public Optional|SessionFinalScoreData|null $final_score,
    ) {}
}

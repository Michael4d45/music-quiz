<?php

declare(strict_types=1);

namespace App\Data\Models;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class UserData extends Data
{
    public function __construct(
        public string $id,
        public null|string $name,
        public bool $is_admin,
        public null|string $email,
        public bool $is_guest,
        public null|string $google_id,
        public null|string $verified_google_email,
        public null|Carbon $email_verified_at,
        public null|Carbon $created_at,
        public null|Carbon $updated_at,
        public null|Carbon $deleted_at,
        /** @var Collection<array-key,GameSessionData>|Optional */
        public Collection|Optional $game_sessions,
        /** @var Collection<array-key,SessionParticipantData>|Optional */
        public Collection|Optional $participants,
        /** @var Collection<array-key,UserStatisticData>|Optional */
        public Collection|Optional $statistics,
        /** @var Collection<array-key,PlaylistData>|Optional */
        public Collection|Optional $playlists,
        /** @var Collection<array-key,QuizQuestionData>|Optional */
        public Collection|Optional $quiz_questions,
        /** @var Collection<array-key,MusicTrackData>|Optional */
        public Collection|Optional $music_tracks,
    ) {}
}

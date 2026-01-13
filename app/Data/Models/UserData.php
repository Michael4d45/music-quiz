<?php

declare(strict_types=1);

namespace App\Data\Models;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\AutoWhenLoadedLazy;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

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
        /** @var Collection<array-key,GameSessionData>|Lazy $game_sessions */
        #[AutoWhenLoadedLazy('gameSessions')]
        public Collection|Lazy $game_sessions,
        /** @var Collection<array-key,SessionParticipantData>|Lazy $participants */
        #[AutoWhenLoadedLazy]
        public Collection|Lazy $participants,
        /** @var Collection<array-key,UserStatisticData>|Lazy $statistics */
        #[AutoWhenLoadedLazy]
        public Collection|Lazy $statistics,
        /** @var Collection<array-key,PlaylistData>|Lazy $playlists */
        #[AutoWhenLoadedLazy]
        public Collection|Lazy $playlists,
        /** @var Collection<array-key,QuizQuestionData>|Lazy $quiz_questions */
        #[AutoWhenLoadedLazy('quizQuestions')]
        public Collection|Lazy $quiz_questions,
        /** @var Collection<array-key,MusicTrackData>|Lazy $music_tracks */
        #[AutoWhenLoadedLazy('musicTracks')]
        public Collection|Lazy $music_tracks,
    ) {}
}

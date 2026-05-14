<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\EventType;
use App\Enums\QuestionType;
use App\Enums\Role;
use App\Enums\SessionStatus;
use App\Models\AnswerVariant;
use App\Models\Category;
use App\Models\GameSession;
use App\Models\MultipleChoiceOption;
use App\Models\MusicSource;
use App\Models\MusicTrack;
use App\Models\PlayerAnswer;
use App\Models\Playlist;
use App\Models\PlaylistItem;
use App\Models\QuizMode;
use App\Models\QuizQuestion;
use App\Models\ScoringRule;
use App\Models\SessionEvent;
use App\Models\SessionFinalScore;
use App\Models\SessionParticipant;
use App\Models\SessionRound;
use App\Models\SourceApiCredential;
use App\Models\SubCategory;
use App\Models\TrackAvailability;
use App\Models\TrackSourceLink;
use App\Models\User;
use App\Models\UserStatistic;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Phase 1: Foundation data
        $this->command->info('Creating foundation data...');
        $this->seedFoundationData();

        // Phase 2: Content data
        $this->command->info('Creating content data...');
        $this->seedContentData();

        // Phase 3: User content
        $this->command->info('Creating user content...');
        $this->seedUserContent();

        // Phase 4: Game activity
        $this->command->info('Creating game activity...');
        $this->seedGameActivity();

        // Phase 5: Statistics
        $this->command->info('Updating statistics...');
        $this->updateUserStatistics(User::all());

        $this->command->info('Seeding completed successfully!');
    }

    private function createAnswerVariants(): void
    {
        $questions = QuizQuestion::all();
        $tracks = MusicTrack::all();

        foreach ($questions as $question) {
            if ($question->question_type === QuestionType::MultipleChoice) {
                // Create 4 multiple choice options
                $options = [$question->correct_answer]; // Correct answer

                // Add 3 wrong options from other tracks by same artist or similar
                $wrongTracks = $tracks
                    ->where('id', '!=', $question->track_id)
                    ->random(3);
                foreach ($wrongTracks as $wrongTrack) {
                    $options[] = $wrongTrack->title;
                }

                shuffle($options);

                foreach ($options as $index => $option) {
                    MultipleChoiceOption::create([
                        'question_id' => $question->id,
                        'option_text' => $option,
                        'is_correct' => $option === $question->correct_answer,
                        'sort_order' => $index + 1,
                    ]);
                }
            } else {
                // Create wrong answer variants for other question types
                $wrongAnswers = [];

                switch ($question->question_type) {
                    case QuestionType::Artist:
                        $wrongAnswers = $tracks
                            ->where('id', '!=', $question->track_id)
                            ->pluck('artist_name')
                            ->unique()
                            ->random(min(3, $tracks->count() - 1))
                            ->toArray();
                        break;

                    case QuestionType::Title:
                        $wrongAnswers = $tracks
                            ->where('id', '!=', $question->track_id)
                            ->pluck('title')
                            ->random(min(3, $tracks->count() - 1))
                            ->toArray();
                        break;

                    case QuestionType::Year:
                        $currentYear = (int) $question->correct_answer;
                        $wrongAnswers = [
                            (string) ($currentYear + rand(1, 5)),
                            (string) ($currentYear - rand(1, 5)),
                            (string) ($currentYear + rand(6, 10)),
                        ];
                        break;

                    case QuestionType::Lyric:
                        $wrongAnswers = [
                            'I want to hold your heart',
                            'I want to hold your soul',
                            'I want to hold your dreams',
                        ];
                        break;
                }

                foreach ($wrongAnswers as $wrongAnswer) {
                    AnswerVariant::create([
                        'question_id' => $question->id,
                        'accepted_text' => $wrongAnswer,
                    ]);
                }
            }
        }
    }

    /**
     * @return Collection<int, Category>
     */
    private function createCategories(): Collection
    {
        $categoriesData = [
            [
                'name' => 'Pop',
                'description' => 'Popular music from various eras',
                'icon_url' => '🎵',
                'sort_order' => 1,
                'subcategories' => [
                    '80s Pop',
                    '90s Pop',
                    '2000s Pop',
                    'Current Pop',
                    'Pop Classics',
                ],
            ],
            [
                'name' => 'Rock',
                'description' => 'Rock music and its subgenres',
                'icon_url' => '🎸',
                'sort_order' => 2,
                'subcategories' => [
                    'Classic Rock',
                    'Alternative Rock',
                    'Indie Rock',
                    'Punk Rock',
                    'Progressive Rock',
                ],
            ],
            [
                'name' => 'Hip-Hop/Rap',
                'description' => 'Hip-hop, rap, and urban music',
                'icon_url' => '🎤',
                'sort_order' => 3,
                'subcategories' => [
                    'Old School Hip-Hop',
                    '90s Rap',
                    '2000s Rap',
                    'Modern Rap',
                    'Conscious Hip-Hop',
                ],
            ],
            [
                'name' => 'Electronic/Dance',
                'description' => 'Electronic and dance music',
                'icon_url' => '🎧',
                'sort_order' => 4,
                'subcategories' => [
                    'House',
                    'Techno',
                    'EDM',
                    'Dubstep',
                    'Ambient',
                ],
            ],
            [
                'name' => 'Jazz',
                'description' => 'Jazz and jazz fusion',
                'icon_url' => '🎷',
                'sort_order' => 5,
                'subcategories' => [
                    'Traditional Jazz',
                    'Bebop',
                    'Cool Jazz',
                    'Jazz Fusion',
                    'Modern Jazz',
                ],
            ],
            [
                'name' => 'Classical',
                'description' => 'Classical music',
                'icon_url' => '🎼',
                'sort_order' => 6,
                'subcategories' => [
                    'Baroque',
                    'Classical Period',
                    'Romantic',
                    'Modern Classical',
                    'Opera',
                ],
            ],
            [
                'name' => 'Country',
                'description' => 'Country and folk music',
                'icon_url' => '🤠',
                'sort_order' => 7,
                'subcategories' => [
                    'Classic Country',
                    'Modern Country',
                    'Bluegrass',
                    'Folk',
                    'Americana',
                ],
            ],
            [
                'name' => 'R&B/Soul',
                'description' => 'Rhythm and blues, soul music',
                'icon_url' => '💃',
                'sort_order' => 8,
                'subcategories' => [
                    'Classic R&B',
                    'Contemporary R&B',
                    'Soul',
                    'Motown',
                    'Neo-Soul',
                ],
            ],
        ];

        $categories = collect();

        foreach ($categoriesData as $categoryData) {
            $subcategories = $categoryData['subcategories'];
            unset($categoryData['subcategories']);

            $category = Category::create($categoryData);

            foreach ($subcategories as $index => $subName) {
                SubCategory::create([
                    'category_id' => $category->id,
                    'name' => $subName,
                    'description' => "Songs from the {$subName} genre",
                    'sort_order' => $index + 1,
                ]);
            }

            $categories->push($category);
        }

        return $categories;
    }

    /**
     * @param Collection<int, MusicTrack> $tracks
     * @param Collection<int, MusicSource> $sources
     */
    // Each track gets 2-4 random source links
    // 80% verified
    // 90% available
    // Create track availability for some regions
    // Create 2-5 playlists per user
    // Not all users create playlists
    // Add 10-25 questions to each playlist
    /**
     * @param Collection<int, User> $users
     * @param Collection<int, QuizMode> $modes
     * @param Collection<int, ScoringRule> $rules
     * @param Collection<int, Playlist> $playlists
     * @return Collection<int, GameSession>
     */
    private function createGameSessions(
        Collection $users,
        Collection $modes,
        Collection $rules,
        Collection $playlists,
    ): Collection {
        $sessionsData = [];
        $powerUsers = $users->random(10); // 10 power users host most games
        $regularUsers = $users->diff($powerUsers)->random(40); // 40 regular users
        $sessions = collect();

        // Create sessions with realistic distribution
        $sessionCounts = [
            SessionStatus::Lobby->value => 60, // 60 sessions waiting for players
            SessionStatus::InProgress->value => 45, // 45 active games
            SessionStatus::RoundTransition->value => 15, // 15 in transition
            SessionStatus::Paused->value => 10, // 10 paused
            SessionStatus::Completed->value => 170, // 170 completed games
        ];

        foreach ($sessionCounts as $status => $count) {
            for ($i = 0; $i < $count; $i++) {
                // Weighted host selection (power users host more)
                $host = rand(1, 10) <= 7
                    ? $powerUsers->random()
                    : $regularUsers->random();

                $sessionData = [
                    'host_id' => $host->id,
                    'room_code' => strtoupper(Str::random(6)),
                    'status' => $status,
                    'quiz_mode_id' => $modes->random()->id,
                    'scoring_rule_id' => $rules->random()->id,
                    'max_players' => rand(2, 8),
                    'is_public' =>
                        $status === SessionStatus::Lobby->value
                            && rand(1, 10) <= 5,
                    'created_at' => now()->subDays(rand(0, 30)),
                    'updated_at' => now(),
                ];

                // Sometimes assign a playlist to the session (30% chance)
                if ($playlists->isNotEmpty() && rand(1, 10) <= 3) {
                    $playlist = $playlists->random();
                    $sessionData['playlist_id'] = $playlist->id;
                    // Increment play count
                    $playlist->increment('play_count');
                }

                // Add timestamps based on status
                if (
                    $status === SessionStatus::InProgress->value
                    || $status === SessionStatus::RoundTransition->value
                ) {
                    $sessionData['started_at'] = now()->subMinutes(rand(
                        5,
                        120,
                    ));
                } elseif ($status === SessionStatus::Completed->value) {
                    $startTime = now()->subHours(rand(1, 24));
                    $sessionData['started_at'] = $startTime;
                    $sessionData['ended_at'] = $startTime->addMinutes(rand(
                        30,
                        120,
                    ));
                }

                $session = GameSession::create($sessionData);
                $sessions->push($session);
            }
        }

        return $sessions;
    }

    /**
     * @return Collection<int, MusicSource>
     */
    private function createMusicSources(): Collection
    {
        $sourcesData = [
            [
                'name' => 'spotify',
                'display_name' => 'Spotify',
                'api_base_url' => 'https://api.spotify.com/v1/',
                'requires_authentication' => true,
                'is_active' => true,
                'priority' => 1,
            ],
            [
                'name' => 'apple_music',
                'display_name' => 'Apple Music',
                'api_base_url' => 'https://api.music.apple.com/v1/',
                'requires_authentication' => true,
                'is_active' => true,
                'priority' => 2,
            ],
            [
                'name' => 'youtube_music',
                'display_name' => 'YouTube Music',
                'api_base_url' => 'https://www.googleapis.com/youtube/v3/',
                'requires_authentication' => true,
                'is_active' => true,
                'priority' => 3,
            ],
            [
                'name' => 'soundcloud',
                'display_name' => 'SoundCloud',
                'api_base_url' => 'https://api.soundcloud.com/',
                'requires_authentication' => true,
                'is_active' => true,
                'priority' => 4,
            ],
            [
                'name' => 'deezer',
                'display_name' => 'Deezer',
                'api_base_url' => 'https://api.deezer.com/',
                'requires_authentication' => false,
                'is_active' => true,
                'priority' => 5,
            ],
        ];

        $sources = collect();

        foreach ($sourcesData as $sourceData) {
            $source = MusicSource::create($sourceData);
            $sources->push($source);

            if ($source->requires_authentication) {
                SourceApiCredential::create([
                    'source_id' => $source->id,
                    'credential_type' => 'api_key',
                    'encrypted_value' => Str::random(32),
                    'expires_at' => now()->addYear(),
                ]);
            }
        }

        MusicSource::query()->firstOrCreate(['name' => 'user_upload'], [
            'display_name' => 'My audio file (upload)',
            'api_base_url' => null,
            'requires_authentication' => false,
            'is_active' => true,
            'priority' => 99,
        ]);

        return $sources;
    }

    /**
     * @param Collection<int, SubCategory> $subCategories
     * @param Collection<int, MusicSource> $sources
     * @return Collection<int, MusicTrack>
     */
    private function createMusicTracks(
        Collection $subCategories,
        Collection $sources,
    ): Collection {
        $trackData = [
            [
                'title' => 'Billie Jean',
                'artist' => 'Michael Jackson',
                'year' => 1983,
                'genre' => 'Pop',
                'duration' => 293,
            ],
            [
                'title' => 'Thriller',
                'artist' => 'Michael Jackson',
                'year' => 1983,
                'genre' => 'Pop',
                'duration' => 357,
            ],
            [
                'title' => 'Beat It',
                'artist' => 'Michael Jackson',
                'year' => 1983,
                'genre' => 'Pop',
                'duration' => 258,
            ],
            [
                'title' => 'Wannabe',
                'artist' => 'Spice Girls',
                'year' => 1996,
                'genre' => 'Pop',
                'duration' => 173,
            ],
            [
                'title' => 'Say You\'ll Be There',
                'artist' => 'Spice Girls',
                'year' => 1996,
                'genre' => 'Pop',
                'duration' => 235,
            ],
            [
                'title' => 'Oops!... I Did It Again',
                'artist' => 'Britney Spears',
                'year' => 2000,
                'genre' => 'Pop',
                'duration' => 211,
            ],
            [
                'title' => '...Baby One More Time',
                'artist' => 'Britney Spears',
                'year' => 1998,
                'genre' => 'Pop',
                'duration' => 211,
            ],
            [
                'title' => 'Bye Bye Bye',
                'artist' => 'NSYNC',
                'year' => 2000,
                'genre' => 'Pop',
                'duration' => 195,
            ],
            [
                'title' => 'I Try',
                'artist' => 'Macy Gray',
                'year' => 1999,
                'genre' => 'Pop',
                'duration' => 235,
            ],
            [
                'title' => 'Maria Maria',
                'artist' => 'Santana ft. The Product G&B',
                'year' => 2000,
                'genre' => 'Pop',
                'duration' => 263,
            ],
            [
                'title' => 'Independent Women',
                'artist' => 'Destiny\'s Child',
                'year' => 2000,
                'genre' => 'Pop',
                'duration' => 221,
            ],
            [
                'title' => 'Say My Name',
                'artist' => 'Destiny\'s Child',
                'year' => 1999,
                'genre' => 'Pop',
                'duration' => 271,
            ],
            [
                'title' => 'Thong Song',
                'artist' => 'Sisqo',
                'year' => 2000,
                'genre' => 'Pop',
                'duration' => 238,
            ],
            [
                'title' => 'Try Again',
                'artist' => 'Aaliyah',
                'year' => 2000,
                'genre' => 'Pop',
                'duration' => 264,
            ],
            [
                'title' => 'I Wanna Know',
                'artist' => 'Joe',
                'year' => 2000,
                'genre' => 'Pop',
                'duration' => 296,
            ],
            [
                'title' => 'All Good Things',
                'artist' => 'Nelly Furtado',
                'year' => 2000,
                'genre' => 'Pop',
                'duration' => 265,
            ],
            [
                'title' => 'I\'m Like a Bird',
                'artist' => 'Nelly Furtado',
                'year' => 2000,
                'genre' => 'Pop',
                'duration' => 243,
            ],
            [
                'title' => 'Jumpin\' Jumpin\'',
                'artist' => 'Destiny\'s Child',
                'year' => 2000,
                'genre' => 'Pop',
                'duration' => 226,
            ],
            [
                'title' => 'No More (Baby I\'ma Do Right)',
                'artist' => '3LW',
                'year' => 2000,
                'genre' => 'Pop',
                'duration' => 245,
            ],
            [
                'title' => 'He Loves U Not',
                'artist' => 'Dream',
                'year' => 2000,
                'genre' => 'Pop',
                'duration' => 216,
            ],

            [
                'title' => 'Stairway to Heaven',
                'artist' => 'Led Zeppelin',
                'year' => 1971,
                'genre' => 'Rock',
                'duration' => 482,
            ],
            [
                'title' => 'Bohemian Rhapsody',
                'artist' => 'Queen',
                'year' => 1975,
                'genre' => 'Rock',
                'duration' => 355,
            ],
            [
                'title' => 'Hotel California',
                'artist' => 'Eagles',
                'year' => 1977,
                'genre' => 'Rock',
                'duration' => 391,
            ],
            [
                'title' => 'More Than a Feeling',
                'artist' => 'Boston',
                'year' => 1976,
                'genre' => 'Rock',
                'duration' => 285,
            ],
            [
                'title' => 'Dream On',
                'artist' => 'Aerosmith',
                'year' => 1973,
                'genre' => 'Rock',
                'duration' => 268,
            ],
            [
                'title' => 'Free Bird',
                'artist' => 'Lynyrd Skynyrd',
                'year' => 1973,
                'genre' => 'Rock',
                'duration' => 548,
            ],
            [
                'title' => 'Sweet Child O\' Mine',
                'artist' => 'Guns N\' Roses',
                'year' => 1987,
                'genre' => 'Rock',
                'duration' => 356,
            ],
            [
                'title' => 'Welcome to the Jungle',
                'artist' => 'Guns N\' Roses',
                'year' => 1987,
                'genre' => 'Rock',
                'duration' => 273,
            ],
            [
                'title' => 'Livin\' on a Prayer',
                'artist' => 'Bon Jovi',
                'year' => 1986,
                'genre' => 'Rock',
                'duration' => 249,
            ],
            [
                'title' => 'You Give Love a Bad Name',
                'artist' => 'Bon Jovi',
                'year' => 1986,
                'genre' => 'Rock',
                'duration' => 223,
            ],
            [
                'title' => 'Smells Like Teen Spirit',
                'artist' => 'Nirvana',
                'year' => 1991,
                'genre' => 'Rock',
                'duration' => 301,
            ],
            [
                'title' => 'Come As You Are',
                'artist' => 'Nirvana',
                'year' => 1991,
                'genre' => 'Rock',
                'duration' => 219,
            ],
            [
                'title' => 'Wonderwall',
                'artist' => 'Oasis',
                'year' => 1995,
                'genre' => 'Rock',
                'duration' => 258,
            ],
            [
                'title' => 'Don\'t Look Back in Anger',
                'artist' => 'Oasis',
                'year' => 1995,
                'genre' => 'Rock',
                'duration' => 290,
            ],
            [
                'title' => 'Creep',
                'artist' => 'Radiohead',
                'year' => 1993,
                'genre' => 'Rock',
                'duration' => 238,
            ],
            [
                'title' => 'Karma Police',
                'artist' => 'Radiohead',
                'year' => 1997,
                'genre' => 'Rock',
                'duration' => 264,
            ],
            [
                'title' => 'Bitter Sweet Symphony',
                'artist' => 'The Verve',
                'year' => 1997,
                'genre' => 'Rock',
                'duration' => 357,
            ],
            [
                'title' => 'The Drugs Don\'t Work',
                'artist' => 'The Verve',
                'year' => 1997,
                'genre' => 'Rock',
                'duration' => 304,
            ],
            [
                'title' => 'Black Hole Sun',
                'artist' => 'Soundgarden',
                'year' => 1994,
                'genre' => 'Rock',
                'duration' => 318,
            ],
            [
                'title' => 'Alive',
                'artist' => 'Pearl Jam',
                'year' => 1991,
                'genre' => 'Rock',
                'duration' => 341,
            ],

            [
                'title' => 'Fight the Power',
                'artist' => 'Public Enemy',
                'year' => 1989,
                'genre' => 'Hip-Hop',
                'duration' => 298,
            ],
            [
                'title' => 'Straight Outta Compton',
                'artist' => 'N.W.A.',
                'year' => 1988,
                'genre' => 'Hip-Hop',
                'duration' => 261,
            ],
            [
                'title' => 'Juicy',
                'artist' => 'The Notorious B.I.G.',
                'year' => 1994,
                'genre' => 'Hip-Hop',
                'duration' => 302,
            ],
            [
                'title' => 'Big Poppa',
                'artist' => 'The Notorious B.I.G.',
                'year' => 1994,
                'genre' => 'Hip-Hop',
                'duration' => 253,
            ],
            [
                'title' => 'Shoop',
                'artist' => 'Salt-N-Pepa',
                'year' => 1993,
                'genre' => 'Hip-Hop',
                'duration' => 249,
            ],
            [
                'title' => 'Push It',
                'artist' => 'Salt-N-Pepa',
                'year' => 1987,
                'genre' => 'Hip-Hop',
                'duration' => 284,
            ],
            [
                'title' => 'Rapper\'s Delight',
                'artist' => 'The Sugarhill Gang',
                'year' => 1979,
                'genre' => 'Hip-Hop',
                'duration' => 888,
            ],
            [
                'title' => 'The Message',
                'artist' => 'Grandmaster Flash and the Furious Five',
                'year' => 1982,
                'genre' => 'Hip-Hop',
                'duration' => 434,
            ],
            [
                'title' => 'Paid in Full',
                'artist' => 'Eric B. & Rakim',
                'year' => 1987,
                'genre' => 'Hip-Hop',
                'duration' => 222,
            ],
            [
                'title' => 'My Melody',
                'artist' => 'Eric B. & Rakim',
                'year' => 1987,
                'genre' => 'Hip-Hop',
                'duration' => 361,
            ],
            [
                'title' => 'It Takes Two',
                'artist' => 'Rob Base & DJ EZ Rock',
                'year' => 1988,
                'genre' => 'Hip-Hop',
                'duration' => 307,
            ],
            [
                'title' => 'The Roof',
                'artist' => 'Mariah Carey',
                'year' => 1997,
                'genre' => 'Hip-Hop',
                'duration' => 324,
            ],
            [
                'title' => 'Fantasy',
                'artist' => 'Mariah Carey',
                'year' => 1995,
                'genre' => 'Hip-Hop',
                'duration' => 243,
            ],
            [
                'title' => 'Vision of Love',
                'artist' => 'Mariah Carey',
                'year' => 1990,
                'genre' => 'Hip-Hop',
                'duration' => 214,
            ],
            [
                'title' => 'Always Be My Baby',
                'artist' => 'Mariah Carey',
                'year' => 1995,
                'genre' => 'Hip-Hop',
                'duration' => 278,
            ],
            [
                'title' => 'Honey',
                'artist' => 'Mariah Carey',
                'year' => 1997,
                'genre' => 'Hip-Hop',
                'duration' => 309,
            ],

            [
                'title' => 'Levels',
                'artist' => 'Avicii',
                'year' => 2011,
                'genre' => 'Electronic',
                'duration' => 201,
            ],
            [
                'title' => 'Wake Me Up',
                'artist' => 'Avicii',
                'year' => 2013,
                'genre' => 'Electronic',
                'duration' => 247,
            ],
            [
                'title' => 'Animals',
                'artist' => 'Martin Garrix',
                'year' => 2013,
                'genre' => 'Electronic',
                'duration' => 177,
            ],
            [
                'title' => 'Tsunami',
                'artist' => 'DVBBS & Borgeous',
                'year' => 2013,
                'genre' => 'Electronic',
                'duration' => 188,
            ],
            [
                'title' => 'Boneless',
                'artist' => 'Steve Aoki, Chris Lake & Tujamo',
                'year' => 2014,
                'genre' => 'Electronic',
                'duration' => 181,
            ],
            [
                'title' => 'Epic',
                'artist' => 'Sasha',
                'year' => 1994,
                'genre' => 'Electronic',
                'duration' => 402,
            ],
            [
                'title' => 'Rez',
                'artist' => 'Underworld',
                'year' => 1993,
                'genre' => 'Electronic',
                'duration' => 531,
            ],
            [
                'title' => 'Windowlicker',
                'artist' => 'Aphex Twin',
                'year' => 1999,
                'genre' => 'Electronic',
                'duration' => 397,
            ],
            [
                'title' => 'Porcelain',
                'artist' => 'Moby',
                'year' => 1999,
                'genre' => 'Electronic',
                'duration' => 241,
            ],
            [
                'title' => 'Natural Blues',
                'artist' => 'Moby',
                'year' => 1999,
                'genre' => 'Electronic',
                'duration' => 241,
            ],

            [
                'title' => 'Take Five',
                'artist' => 'Dave Brubeck',
                'year' => 1959,
                'genre' => 'Jazz',
                'duration' => 324,
            ],
            [
                'title' => 'So What',
                'artist' => 'Miles Davis',
                'year' => 1959,
                'genre' => 'Jazz',
                'duration' => 562,
            ],
            [
                'title' => 'My Funny Valentine',
                'artist' => 'Chet Baker',
                'year' => 1954,
                'genre' => 'Jazz',
                'duration' => 148,
            ],
            [
                'title' => 'Round Midnight',
                'artist' => 'Thelonious Monk',
                'year' => 1947,
                'genre' => 'Jazz',
                'duration' => 189,
            ],
            [
                'title' => 'Blue in Green',
                'artist' => 'Miles Davis',
                'year' => 1959,
                'genre' => 'Jazz',
                'duration' => 337,
            ],
            [
                'title' => 'In a Silent Way',
                'artist' => 'Miles Davis',
                'year' => 1969,
                'genre' => 'Jazz',
                'duration' => 187,
            ],
            [
                'title' => 'Giant Steps',
                'artist' => 'John Coltrane',
                'year' => 1959,
                'genre' => 'Jazz',
                'duration' => 281,
            ],
            [
                'title' => 'A Love Supreme',
                'artist' => 'John Coltrane',
                'year' => 1964,
                'genre' => 'Jazz',
                'duration' => 475,
            ],
            [
                'title' => 'Song for My Father',
                'artist' => 'Horace Silver',
                'year' => 1964,
                'genre' => 'Jazz',
                'duration' => 456,
            ],
            [
                'title' => 'Maiden Voyage',
                'artist' => 'Herbie Hancock',
                'year' => 1965,
                'genre' => 'Jazz',
                'duration' => 468,
            ],

            [
                'title' => 'The Four Seasons - Spring',
                'artist' => 'Antonio Vivaldi',
                'year' => 1723,
                'genre' => 'Classical',
                'duration' => 618,
            ],
            [
                'title' => 'Canon in D',
                'artist' => 'Johann Pachelbel',
                'year' => 1680,
                'genre' => 'Classical',
                'duration' => 336,
            ],
            [
                'title' => 'Moonlight Sonata',
                'artist' => 'Ludwig van Beethoven',
                'year' => 1801,
                'genre' => 'Classical',
                'duration' => 840,
            ],
            [
                'title' => 'Für Elise',
                'artist' => 'Ludwig van Beethoven',
                'year' => 1810,
                'genre' => 'Classical',
                'duration' => 176,
            ],
            [
                'title' => 'Symphony No. 5',
                'artist' => 'Ludwig van Beethoven',
                'year' => 1808,
                'genre' => 'Classical',
                'duration' => 448,
            ],
            [
                'title' => 'The Nutcracker Suite',
                'artist' => 'Pyotr Ilyich Tchaikovsky',
                'year' => 1892,
                'genre' => 'Classical',
                'duration' => 1320,
            ],
            [
                'title' => 'Swan Lake',
                'artist' => 'Pyotr Ilyich Tchaikovsky',
                'year' => 1876,
                'genre' => 'Classical',
                'duration' => 720,
            ],
            [
                'title' => 'Clair de Lune',
                'artist' => 'Claude Debussy',
                'year' => 1905,
                'genre' => 'Classical',
                'duration' => 297,
            ],
            [
                'title' => 'Boléro',
                'artist' => 'Maurice Ravel',
                'year' => 1928,
                'genre' => 'Classical',
                'duration' => 972,
            ],
            [
                'title' => 'The Carnival of the Animals',
                'artist' => 'Camille Saint-Saëns',
                'year' => 1886,
                'genre' => 'Classical',
                'duration' => 1320,
            ],

            [
                'title' => 'Friends in Low Places',
                'artist' => 'Garth Brooks',
                'year' => 1990,
                'genre' => 'Country',
                'duration' => 244,
            ],
            [
                'title' => 'Wagon Wheel',
                'artist' => 'Old Crow Medicine Show',
                'year' => 2008,
                'genre' => 'Country',
                'duration' => 198,
            ],
            [
                'title' => 'Take Me Home, Country Roads',
                'artist' => 'John Denver',
                'year' => 1971,
                'genre' => 'Country',
                'duration' => 197,
            ],
            [
                'title' => 'Jolene',
                'artist' => 'Dolly Parton',
                'year' => 1973,
                'genre' => 'Country',
                'duration' => 162,
            ],
            [
                'title' => 'Coat of Many Colors',
                'artist' => 'Dolly Parton',
                'year' => 1971,
                'genre' => 'Country',
                'duration' => 186,
            ],
            [
                'title' => 'Ring of Fire',
                'artist' => 'Johnny Cash',
                'year' => 1963,
                'genre' => 'Country',
                'duration' => 157,
            ],
            [
                'title' => 'Folsom Prison Blues',
                'artist' => 'Johnny Cash',
                'year' => 1955,
                'genre' => 'Country',
                'duration' => 167,
            ],
            [
                'title' => 'I Walk the Line',
                'artist' => 'Johnny Cash',
                'year' => 1956,
                'genre' => 'Country',
                'duration' => 165,
            ],
            [
                'title' => 'Stand By Your Man',
                'artist' => 'Tammy Wynette',
                'year' => 1968,
                'genre' => 'Country',
                'duration' => 155,
            ],
            [
                'title' => 'Coal Miner\'s Daughter',
                'artist' => 'Loretta Lynn',
                'year' => 1970,
                'genre' => 'Country',
                'duration' => 186,
            ],

            [
                'title' => 'What\'s Going On',
                'artist' => 'Marvin Gaye',
                'year' => 1971,
                'genre' => 'R&B',
                'duration' => 233,
            ],
            [
                'title' => 'Let\'s Get It On',
                'artist' => 'Marvin Gaye',
                'year' => 1973,
                'genre' => 'R&B',
                'duration' => 285,
            ],
            [
                'title' => 'Superstition',
                'artist' => 'Stevie Wonder',
                'year' => 1972,
                'genre' => 'R&B',
                'duration' => 271,
            ],
            [
                'title' => 'Sir Duke',
                'artist' => 'Stevie Wonder',
                'year' => 1976,
                'genre' => 'R&B',
                'duration' => 229,
            ],
            [
                'title' => 'Respect',
                'artist' => 'Aretha Franklin',
                'year' => 1967,
                'genre' => 'R&B',
                'duration' => 147,
            ],
            [
                'title' => 'Chain of Fools',
                'artist' => 'Aretha Franklin',
                'year' => 1967,
                'genre' => 'R&B',
                'duration' => 170,
            ],
            [
                'title' => 'At Last',
                'artist' => 'Etta James',
                'year' => 1960,
                'genre' => 'R&B',
                'duration' => 181,
            ],
            [
                'title' => 'Hit Me With Your Best Shot',
                'artist' => 'Pat Benatar',
                'year' => 1980,
                'genre' => 'R&B',
                'duration' => 171,
            ],
            [
                'title' => 'Man in the Mirror',
                'artist' => 'Michael Jackson',
                'year' => 1987,
                'genre' => 'R&B',
                'duration' => 318,
            ],
            [
                'title' => 'Smooth',
                'artist' => 'Santana ft. Rob Thomas',
                'year' => 1999,
                'genre' => 'R&B',
                'duration' => 295,
            ],
        ];

        $tracks = collect();

        assert(
            $subCategories->isNotEmpty(),
            'SubCategories collection should not be empty',
        );
        $defaultSubCategory = $subCategories->first();
        $fallbackSubCategory = $subCategories->get(5) ?? $defaultSubCategory;
        /** @var array<string, string> $genreMap */
        $genreMap = [
            'Pop' =>
                $subCategories->where('name', 'like', '%Pop%')->first()->id
                    ?? $defaultSubCategory->id,
            'Rock' =>
                $subCategories->where('name', 'like', '%Rock%')->first()->id
                    ?? $fallbackSubCategory->id,
            'Hip-Hop' =>
                $subCategories->where('name', 'like', '%Rap%')->first()->id
                    ?? $fallbackSubCategory->id,
            'Electronic' =>
                $subCategories->where('name', 'like', '%House%')->first()->id
                    ?? $fallbackSubCategory->id,
            'Jazz' =>
                $subCategories->where('name', 'like', '%Jazz%')->first()->id
                    ?? $fallbackSubCategory->id,
            'Classical' =>
                $subCategories
                    ->where('name', 'like', '%Classical%')
                    ->first()->id ?? $fallbackSubCategory->id,
            'Country' =>
                $subCategories->where('name', 'like', '%Country%')->first()->id
                    ?? $fallbackSubCategory->id,
            'R&B' =>
                $subCategories->where('name', 'like', '%R&B%')->first()->id
                    ?? $fallbackSubCategory->id,
        ];

        foreach ($trackData as $trackInfo) {
            $subCategoryId =
                $genreMap[$trackInfo['genre']] ?? $subCategories->random()->id;
            $primarySourceId = $sources->random()->id;

            $track = MusicTrack::create([
                'title' => $trackInfo['title'],
                'artist_name' => $trackInfo['artist'],
                'album_name' => 'Greatest Hits',
                'release_year' => $trackInfo['year'],
                'genre' => $trackInfo['genre'],
                'duration_ms' => $trackInfo['duration'] * 1000,
                'sub_category_id' => $subCategoryId,
                'primary_source_id' => $primarySourceId,
            ]);

            $tracks->push($track);
        }

        return $tracks;
    }

    /**
     * @param Collection<int, User> $users
     * @param Collection<int, QuizQuestion> $questions
     */
    private function createPlaylists(
        Collection $users,
        Collection $questions,
    ): void {
        foreach ($users->random(50) as $user) {
            $numPlaylists = rand(2, 5);

            for ($i = 0; $i < $numPlaylists; $i++) {
                $playlist = Playlist::create([
                    'user_id' => $user->id,
                    'name' => 'My Quiz Playlist ' . ($i + 1),
                    'description' => 'A collection of great quiz questions',
                    'status' => rand(0, 1) ? 'published' : 'draft',
                    'visibility' => rand(0, 1) ? 'public' : 'private',
                    'tags' => ['rock', 'pop', 'quiz'],
                    'estimated_duration_minutes' => rand(20, 60),
                    'target_audience' => 'General',
                    'question_order' => 'fixed',
                    'default_time_limit_seconds' => 30,
                ]);

                $playlistQuestions = $questions->random(rand(10, 25));

                foreach ($playlistQuestions as $index => $question) {
                    PlaylistItem::create([
                        'playlist_id' => $playlist->id,
                        'question_id' => $question->id,
                        'sort_order' => ($index + 1) * 100,
                        'added_at' => now(),
                    ]);
                }
            }
        }
    }

    private function createQuizModesAndRules(): void
    {
        QuizMode::create([
            'name' => 'Multiple Choice',
            'description' => 'Choose from 4 options',
            'allows_host_override' => false,
            'requires_manual_scoring' => false,
        ]);
        QuizMode::create([
            'name' => 'Type In Answer',
            'description' => 'Type the correct answer',
            'allows_host_override' => false,
            'requires_manual_scoring' => false,
        ]);
        QuizMode::create([
            'name' => 'Buzz In',
            'description' => 'Fastest to buzz in gets to answer',
            'allows_host_override' => true,
            'requires_manual_scoring' => false,
        ]);
        QuizMode::create([
            'name' => 'Host Judged',
            'description' => 'Host decides if answer is correct',
            'allows_host_override' => true,
            'requires_manual_scoring' => true,
        ]);

        ScoringRule::create([
            'name' => 'Standard Scoring',
            'base_points' => 1000,
            'decay_factor' => 0.95,
            'max_time_ms' => 30_000,
            'streak_bonus_enabled' => false,
        ]);

        ScoringRule::create([
            'name' => 'Speed Scoring',
            'base_points' => 1000,
            'decay_factor' => 0.90,
            'max_time_ms' => 20_000,
            'streak_bonus_enabled' => true,
            'streak_multiplier' => 1.2,
        ]);

        ScoringRule::create([
            'name' => 'Hard Mode',
            'base_points' => 2000,
            'decay_factor' => 0.85,
            'max_time_ms' => 15_000,
            'streak_bonus_enabled' => true,
            'streak_multiplier' => 1.5,
        ]);
    }

    /**
     * @param Collection<int, MusicTrack> $tracks
     */
    private function createQuizQuestions(Collection $tracks): void
    {
        $questionsData = [];

        foreach ($tracks as $track) {
            $questionTypes = [
                QuestionType::Artist,
                QuestionType::Title,
                QuestionType::Year,
                QuestionType::MultipleChoice,
                QuestionType::Lyric,
            ];

            foreach ($questionTypes as $questionType) {
                $difficulty = rand(1, 3);
                $basePoints = 10 * $difficulty;

                $question = [
                    'track_id' => $track->id,
                    'question_type' => $questionType,
                    'prompt_text' => '',
                    'correct_answer' => '',
                    'base_points' => $basePoints,
                    'media_start_seconds' => null,
                    'media_end_seconds' => null,
                    'difficulty_level' => $difficulty,
                ];

                switch ($questionType) {
                    case QuestionType::Artist:
                        $question['prompt_text'] = "Who is the artist of the song '{$track->title}'?";
                        $question['correct_answer'] = $track->artist_name;
                        $question['media_start_seconds'] = null;
                        $question['media_end_seconds'] = null;
                        break;

                    case QuestionType::Title:
                        $question['prompt_text'] = "What is the title of this song by {$track->artist_name}?";
                        $question['correct_answer'] = $track->title;
                        $question['media_start_seconds'] = null;
                        $question['media_end_seconds'] = null;
                        break;

                    case QuestionType::Year:
                        $question['prompt_text'] = "In what year was '{$track->title}' by {$track->artist_name} released?";
                        $question['correct_answer'] =
                            (string) $track->release_year;
                        $question['media_start_seconds'] = null;
                        $question['media_end_seconds'] = null;
                        break;

                    case QuestionType::MultipleChoice:
                        $question['prompt_text'] = "Which of these is '{$track->title}' by {$track->artist_name}?";
                        $question['correct_answer'] = $track->title;
                        $question['media_start_seconds'] = null;
                        $question['media_end_seconds'] = null;
                        break;

                    case QuestionType::Lyric:
                        $question['prompt_text'] = "Complete the lyric: 'I want to hold your hand...' (from {$track->artist_name})";
                        $question['correct_answer'] = 'I want to hold your hand';
                        $question['media_start_seconds'] = rand(0, 30);
                        $question['media_end_seconds'] =
                            $question['media_start_seconds'] + rand(10, 30);
                        break;
                }

                $questionsData[] = $question;
            }
        }

        $this->command->info('Creating questions individually...');
        foreach ($questionsData as $questionData) {
            QuizQuestion::create($questionData);
        }

        $this->createAnswerVariants();
    }

    /**
     * @param Collection<int, GameSession> $sessions
     * @param Collection<int, User> $users
     * @param Collection<int, QuizQuestion> $questions
     */
    private function createSessionActivity(
        Collection $sessions,
        Collection $users,
        Collection $questions,
    ): void {
        $participantsData = [];
        $roundsData = [];
        $answersData = [];
        $eventsData = [];
        $finalScoresData = [];

        foreach ($sessions as $session) {
            // Create participants (2-8 players)
            $numParticipants = rand(2, min(8, $session->max_players));
            $participants = collect();

            for ($i = 0; $i < $numParticipants; $i++) {
                $participantData = [
                    'session_id' => $session->id,
                    'role' => Role::Player,
                    'joined_at' => $session->created_at?->addSeconds(rand(
                        0,
                        300,
                    )),
                    'is_connected' => true,
                    'current_total_score' => 0, // Start with 0, will be updated during gameplay
                ];

                // Sometimes create guest participants (30% chance)
                if (rand(1, 10) <= 3) {
                    $participantData['guest_name'] = 'Guest Player ' . ($i + 1);

                    // No user_id for guests
                } else {
                    $user = $users->random();
                    $participantData['user_id'] = $user->id;
                }

                // For buzz-in mode, set buzzed_in_at for some participants
                if (
                    $session->quiz_mode_id
                    && QuizMode::find($session->quiz_mode_id)?->name
                        === 'Buzz In'
                ) {
                    // Randomly set buzzed_in_at for some participants during active rounds
                    if (
                        $session->status === SessionStatus::InProgress
                        && rand(1, 3) === 1
                    ) {
                        $participantData['buzzed_in_at'] = $session->started_at?->addMinutes(rand(
                            1,
                            10,
                        ));
                    }
                }

                $participant = SessionParticipant::create($participantData);
                $participants->push($participant);
                $participantsData[] = $participant->toArray();
            }

            // Find the host participant
            $hostParticipant = $participants->firstWhere(
                'user_id',
                $session->host_id,
            );

            // Create session events
            $eventsData[] = [
                'session_id' => $session->id,
                'event_type' => EventType::RoundStart->value,
                'participant_id' => $hostParticipant?->id,
                'payload' => json_encode(['action' => 'session_started']),
                'created_at' => $session->created_at,
            ];

            // Create PlayerJoin events for each participant
            foreach ($participants as $participant) {
                $eventsData[] = [
                    'session_id' => $session->id,
                    'event_type' => EventType::PlayerJoin->value,
                    'participant_id' => $participant->id,
                    'payload' => json_encode([
                        'player_id' => $participant->user_id,
                    ]),
                    'created_at' => $participant->joined_at,
                ];
            }

            if ($session->started_at) {
                $eventsData[] = [
                    'session_id' => $session->id,
                    'event_type' => EventType::RoundStart->value,
                    'participant_id' => $hostParticipant?->id,
                    'payload' => json_encode(['action' => 'game_started']),
                    'created_at' => $session->started_at,
                ];
            }

            // Only create rounds and answers for completed or in-progress sessions
            if (
                $session->status === SessionStatus::Completed
                || $session->status === SessionStatus::InProgress
            ) {
                $numRounds = $session->status === SessionStatus::Completed
                    ? rand(10, 20)
                    : rand(1, 5);

                for ($roundNum = 1; $roundNum <= $numRounds; $roundNum++) {
                    $question = $questions->random();
                    $roundStart = $session->started_at?->addMinutes((
                        $roundNum - 1
                    )
                    * 2);

                    $roundData = [
                        'session_id' => $session->id,
                        'question_id' => $question->id,
                        'round_number' => $roundNum,
                        'started_at' => $roundStart,
                    ];

                    // Set first buzzer for buzz-in mode
                    if (
                        $session->quiz_mode_id
                        && QuizMode::find($session->quiz_mode_id)?->name
                            === 'Buzz In'
                    ) {
                        // Randomly select a participant as the first buzzer
                        $participantsForRound = $participants->shuffle();
                        $firstBuzzer = $participantsForRound->first();
                        $roundData['first_buzzer_id'] = $firstBuzzer->id;
                    }

                    $round = SessionRound::create($roundData);

                    $roundsData[] = $round->toArray();

                    // Create answers for each participant
                    foreach ($participants as $participant) {
                        // Simulate realistic answer accuracy (use guest_name for guests, user_id for registered users)
                        $userIdentifier =
                            $participant->user_id ?? $participant->guest_name
                                ?? 'guest-' . $participant->id;
                        $isCorrect =
                            $this->simulateAnswerAccuracy($userIdentifier);
                        $answerTime = rand(5, 30); // 5-30 seconds to answer

                        $answerData = [
                            'participant_id' => $participant->id,
                            'round_id' => $round->id,
                            'is_correct' => $isCorrect,
                            'response_time_ms' => $answerTime * 1000, // Convert to milliseconds
                            'points_awarded' => $isCorrect
                                ? $question->base_points
                                : 0,
                        ];

                        if (
                            $question->question_type
                            === QuestionType::MultipleChoice
                        ) {
                            $this->setMultipleChoiceAnswerData(
                                $answerData,
                                $question,
                                $isCorrect,
                            );
                        } else {
                            $this->setTextAnswerData(
                                $answerData,
                                $question,
                                $isCorrect,
                            );
                        }

                        $answer = PlayerAnswer::create($answerData);
                        $answersData[] = $answer->toArray();

                        // Update participant's current total score
                        $participant->increment(
                            'current_total_score',
                            $answer->points_awarded,
                        );

                        // Create AnswerSubmitted event
                        $answerTime = $roundStart?->addSeconds($answerTime);
                        $eventsData[] = [
                            'session_id' => $session->id,
                            'event_type' => EventType::AnswerSubmitted->value,
                            'participant_id' => $participant->id,
                            'payload' => json_encode([
                                'round_id' => $round->id,
                                'answer_id' => $answer->id,
                                'is_correct' => $isCorrect,
                                'response_time_ms' => $answer->response_time_ms,
                            ]),
                            'created_at' => $answerTime,
                        ];
                    }

                    // Create RoundEnd event after all participants have answered
                    $roundEndTime = $roundStart?->addSeconds(35); // Round ends after 35 seconds

                    // Update the round with ended_at
                    $round->update(['ended_at' => $roundEndTime]);

                    $eventsData[] = [
                        'session_id' => $session->id,
                        'event_type' => EventType::RoundEnd->value,
                        'participant_id' => $hostParticipant?->id,
                        'payload' => json_encode([
                            'round_id' => $round->id,
                            'round_number' => $roundNum,
                        ]),
                        'created_at' => $roundEndTime,
                    ];
                }

                // Create final scores for completed sessions
                if ($session->status === SessionStatus::Completed) {
                    $rank = 1;
                    $participantsWithScores = $participants
                        ->map(function ($participant) use (&$rank) {
                            $playerAnswers = PlayerAnswer::where(
                                'participant_id',
                                $participant->id,
                            )->get();

                            $totalScore = $playerAnswers->sum('points_awarded');
                            $questionsAnswered = $playerAnswers->count();
                            $correctAnswers = $playerAnswers
                                ->where('is_correct', true)
                                ->count();
                            $averageResponseTime = $questionsAnswered > 0
                                ? (int) $playerAnswers->avg('response_time_ms')
                                : null;

                            // Calculate longest streak
                            $longestStreak = 0;
                            $currentStreak = 0;
                            foreach ($playerAnswers->sortBy(
                                'created_at',
                            ) as $answer) {
                                if ($answer->is_correct) {
                                    $currentStreak++;
                                    $longestStreak = max(
                                        $longestStreak,
                                        $currentStreak,
                                    );
                                } else {
                                    $currentStreak = 0;
                                }
                            }

                            return [
                                'session_id' => $participant->session_id,
                                'participant_id' => $participant->id,
                                'final_score' => $totalScore,
                                'final_rank' => $rank++,
                                'questions_answered' => $questionsAnswered,
                                'correct_answers' => $correctAnswers,
                                'average_response_time_ms' =>
                                    $averageResponseTime,
                                'longest_streak' => $longestStreak,
                            ];
                        })
                        ->sortByDesc('final_score')
                        ->values();

                    $finalScoresData = array_merge(
                        $finalScoresData,
                        $participantsWithScores->toArray(),
                    );
                }
            }
        }

        // Create final scores individually
        if (count($finalScoresData) > 0) {
            foreach ($finalScoresData as $scoreData) {
                // @phpstan-ignore argument.type
                SessionFinalScore::create($scoreData);
            }
        }

        // Create events individually
        if (count($eventsData) > 0) {
            foreach ($eventsData as $eventData) {
                SessionEvent::create($eventData);
            }
        }
    }

    /**
     * @param Collection<int, MusicTrack> $tracks
     * @param Collection<int, MusicSource> $sources
     */
    /**
     * @param Collection<int, MusicTrack> $tracks
     * @param Collection<int, MusicSource> $sources
     */
    private function createTrackSourceLinks(
        Collection $tracks,
        Collection $sources,
    ): void {
        $linkCount = 0;

        foreach ($tracks as $track) {
            /** @var MusicTrack $track */
            $randomSources = $sources->random(rand(2, 4));

            foreach ($randomSources as $source) {
                /** @var MusicSource $source */
                $externalId = Str::random(22);
                $link = TrackSourceLink::create([
                    'track_id' => $track->id,
                    'source_id' => $source->id,
                    'external_id' => $externalId,
                    'full_url' => $this->generateSourceUrl(
                        $source,
                        $externalId,
                    ),
                    'preview_url' => rand(0, 1)
                        ? $this->generatePreviewUrl($source, $externalId)
                        : null,
                    'is_verified' => rand(0, 10) > 2,
                    'is_available' => rand(0, 10) > 1,
                    'last_checked_at' => now()->subDays(rand(0, 30)),
                ]);

                TrackAvailability::create([
                    'track_source_link_id' => $link->id,
                    'country_code' => ['US', 'GB', 'CA', 'AU', 'DE'][rand(
                        0,
                        4,
                    )],
                    'is_available' => true,
                    'last_checked_at' => now()->subDays(rand(0, 30)),
                ]);

                $linkCount++;
            }
        }

        $this->command->info('Created ' . $linkCount . ' track source links');
    }

    private function generatePreviewUrl(
        MusicSource $source,
        string $externalId,
    ): null|string {
        return match ($source->name) {
            'spotify' => "https://p.scdn.co/mp3-preview/{$externalId}",
            'deezer'
                => "https://cdns-preview-d.deezer.com/stream/{$externalId}",
            default => null,
        };
    }

    private function generateSourceUrl(
        MusicSource $source,
        string $externalId,
    ): string {
        return match ($source->name) {
            'spotify' => "https://open.spotify.com/track/{$externalId}",
            'apple_music' => "https://music.apple.com/us/album/{$externalId}",
            'youtube_music'
                => "https://music.youtube.com/watch?v={$externalId}",
            'soundcloud' => "https://soundcloud.com/track/{$externalId}",
            'deezer' => "https://www.deezer.com/track/{$externalId}",
            default => "https://example.com/track/{$externalId}",
        };
    }

    // Simulate different skill levels
    // Pseudo-random but consistent per user

    // 90% accuracy for experts
    // 75% accuracy for good players
    // 55% accuracy for average players
    // 35% accuracy for struggling players
    // For incorrect answers, randomly select from existing answer variants
    // Fallback if no variants exist
    private function getWrongAnswer(QuizQuestion $question): string
    {
        // Return a wrong answer based on question type
        switch ($question->question_type) {
            case QuestionType::Artist:
                return (
                    MusicTrack::where('id', '!=', $question->track_id)
                        ->inRandomOrder()
                        ->first()
                        ->artist_name ?? 'Unknown Artist'
                );

            case QuestionType::Title:
                return (
                    MusicTrack::where('id', '!=', $question->track_id)
                        ->inRandomOrder()
                        ->first()
                        ->title ?? 'Unknown Title'
                );

            case QuestionType::Year:
                $correctYear = (int) $question->correct_answer;
                return (string) ($correctYear + rand(1, 10));

            case QuestionType::MultipleChoice:
                return (
                    MultipleChoiceOption::where('question_id', $question->id)
                        ->where('is_correct', false)
                        ->inRandomOrder()
                        ->first()
                        ->option_text ?? 'Wrong Answer'
                );

            default:
                return 'Wrong Answer';
        }
    }

    private function seedContentData(): void
    {
        $subCategories = SubCategory::all();
        $sources = MusicSource::all();

        $this->command->info('Creating music tracks...');
        $tracks = $this->createMusicTracks($subCategories, $sources);

        $this->command->info('Creating quiz questions...');
        $this->createQuizQuestions($tracks);

        $this->command->info('Creating track source links...');
        $this->createTrackSourceLinks($tracks, $sources);

        $this->command->info('Content data created successfully!');
    }

    private function seedFoundationData(): void
    {
        $this->command->info('Creating users...');
        User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'is_admin' => true,
            'is_guest' => false,
            'email_verified_at' => now(),
        ]);
        User::factory(75)->create();

        $this->command->info('Creating categories...');
        $categories = $this->createCategories();

        $this->command->info('Creating music sources...');
        $sources = $this->createMusicSources();

        $this->command->info('Creating quiz modes and rules...');
        $this->createQuizModesAndRules();

        $this->command->info('Foundation data created successfully!');
    }

    private function seedGameActivity(): void
    {
        $users = User::all();
        $questions = QuizQuestion::all();
        $modes = QuizMode::all();
        $rules = ScoringRule::all();
        $playlists = Playlist::all();

        $this->command->info('Creating game sessions...');
        $sessions = $this->createGameSessions(
            $users,
            $modes,
            $rules,
            $playlists,
        );

        $this->command->info('Creating session activity...');
        $this->createSessionActivity($sessions, $users, $questions);

        $this->command->info('Game activity created successfully!');
    }

    private function seedUserContent(): void
    {
        $questions = QuizQuestion::all();
        $users = User::all();

        $this->command->info('Creating playlists...');
        $this->createPlaylists($users, $questions);

        $this->command->info('User content created successfully!');
    }

    /**
     * @param array<string, mixed> &$answerData
     */
    private function setMultipleChoiceAnswerData(
        array &$answerData,
        QuizQuestion $question,
        bool $isCorrect,
    ): void {
        if ($isCorrect) {
            $correctOption = MultipleChoiceOption::where(
                'question_id',
                $question->id,
            )
                ->where('is_correct', true)
                ->first();
            if ($correctOption) {
                $answerData['selected_option_id'] = $correctOption->id;
            }
        } else {
            $incorrectOption = MultipleChoiceOption::where(
                'question_id',
                $question->id,
            )
                ->where('is_correct', false)
                ->inRandomOrder()
                ->first();
            if ($incorrectOption) {
                $answerData['selected_option_id'] = $incorrectOption->id;
            }
        }
    }

    /**
     * @param array<string, mixed> &$answerData
     */
    private function setTextAnswerData(
        array &$answerData,
        QuizQuestion $question,
        bool $isCorrect,
    ): void {
        if ($isCorrect) {
            $answerData['submitted_text'] = $question->correct_answer;
        } else {
            $variants = AnswerVariant::where(
                'question_id',
                $question->id,
            )->get();
            if ($variants->isNotEmpty()) {
                $selectedVariant = $variants->random();
                $answerData['submitted_text'] = $selectedVariant->accepted_text;
                $answerData['matched_variant_id'] = $selectedVariant->id;
            } else {
                $answerData['submitted_text'] =
                    $this->getWrongAnswer($question);
            }
        }
    }

    private function simulateAnswerAccuracy(string $userId): bool
    {
        $userSkill = crc32($userId) % 100;

        if ($userSkill > 90) {
            return rand(1, 100) <= 90;
        }
        if ($userSkill > 70) {
            return rand(1, 100) <= 75;
        }
        if ($userSkill > 40) {
            return rand(1, 100) <= 55;
        }
        return rand(1, 100) <= 35;
    }

    /**
     * @param Collection<int, User> $users
     */
    private function updateUserStatistics(Collection $users): void
    {
        $statsData = [];

        foreach ($users as $user) {
            // Calculate user statistics
            $totalGames = SessionParticipant::where(
                'user_id',
                $user->id,
            )->count();
            $completedGames = SessionFinalScore::whereHas('participant', function ($query) use (
                $user,
            ) {
                // @phpstan-ignore argument.type
                $query->where('user_id', $user->id);
            })->count();
            $totalScore = SessionFinalScore::whereHas('participant', function ($query) use (
                $user,
            ) {
                // @phpstan-ignore argument.type
                $query->where('user_id', $user->id);
            })->sum('final_score');
            $bestScore =
                SessionFinalScore::whereHas('participant', function ($query) use (
                    $user,
                ) {
                    // @phpstan-ignore argument.type
                    $query->where('user_id', $user->id);
                })->max('final_score') ?? 0;
            $avgRank = (float) (
                SessionFinalScore::whereHas('participant', function ($query) use (
                    $user,
                ) {
                    $query->where('user_id', $user->id); // @phpstan-ignore argument.type
                })->avg('final_rank') ?? 0
            );

            // Calculate total wins (1st place finishes)
            $totalWins = SessionFinalScore::whereHas('participant', function ($query) use (
                $user,
            ) {
                // @phpstan-ignore argument.type
                $query->where('user_id', $user->id);
            })
                ->where('final_rank', 1)
                ->count();

            // Calculate best streak from all player answers
            $bestStreak = 0;
            $userAnswers = PlayerAnswer::whereHas('participant', function ($query) use (
                $user,
            ) {
                $query->where('user_id', $user->id); // @phpstan-ignore argument.type
            })->orderBy('created_at')->get();

            $currentStreak = 0;
            foreach ($userAnswers as $answer) {
                if ($answer->is_correct) {
                    $currentStreak++;
                    $bestStreak = max($bestStreak, $currentStreak);
                } else {
                    $currentStreak = 0;
                }
            }

            // Find favorite category based on most played
            /** @var object{category_id: string, count: int}|null $favoriteCategory */
            $favoriteCategory = GameSession::where('host_id', $user->id)
                ->join(
                    'session_rounds',
                    'game_sessions.id',
                    '=',
                    'session_rounds.session_id',
                )
                ->join(
                    'quiz_questions',
                    'session_rounds.question_id',
                    '=',
                    'quiz_questions.id',
                )
                ->join(
                    'music_tracks',
                    'quiz_questions.track_id',
                    '=',
                    'music_tracks.id',
                )
                ->join(
                    'sub_categories',
                    'music_tracks.sub_category_id',
                    '=',
                    'sub_categories.id',
                )
                ->selectRaw('sub_categories.category_id, COUNT(*) as count')
                ->groupBy('sub_categories.category_id')
                ->orderByDesc('count')
                ->first();

            $statsData[] = [
                'user_id' => $user->id,
                'total_games_played' => $totalGames,
                'total_wins' => $totalWins,
                'total_points' => $totalScore,
                'average_score' => $totalGames > 0
                    ? round($totalScore / $totalGames, 2)
                    : 0,
                'best_streak' => $bestStreak,
                'favorite_category_id' => $favoriteCategory?->category_id,
            ];
        }

        // Create user statistics individually
        foreach ($statsData as $statData) {
            UserStatistic::create($statData);
        }
    }
}

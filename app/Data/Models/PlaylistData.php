<?php

declare(strict_types=1);

namespace App\Data\Models;


use App\Enums\QuestionOrder;
use App\Enums\Visibility;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class PlaylistData extends Data
{
    public function __construct(
        public string $id,
        public string $user_id,
        public string $name,
        public null|string $description,
        public Visibility $visibility,
        /** @var list<string>|null */
        public null|array $tags,
        public null|int $estimated_duration_minutes,
        public null|string $target_audience,
        public QuestionOrder $question_order,
        public null|int $default_time_limit_seconds,
        public null|string $scoring_rule_id,
        public int $play_count,
        public null|Carbon $created_at,
        public null|Carbon $updated_at,
        /** @var UserData|Optional $user */
        public Optional|UserData $user,
        /** @var Collection<array-key,PlaylistItemData>|Optional */
        public Collection|Optional $items,
        /** @var Collection<array-key,GameSessionData>|Optional */
        public Collection|Optional $game_sessions,
    ) {}
}

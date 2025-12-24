<?php

declare(strict_types=1);

namespace App\Data\Models;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\AutoWhenLoadedLazy;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

class TrackSourceLinkData extends Data
{
    public function __construct(
        public string $id,
        public string $track_id,
        public string $source_id,
        public string $external_id,
        public string|null $preview_url,
        public string|null $full_url,
        public string|null $embed_url,
        public string|null $album_art_url,
        public bool $is_verified,
        public bool $is_available,
        public Carbon|null $last_checked_at,
        public Carbon|null $created_at,
        public Carbon|null $updated_at,
        /** @var MusicTrackData $track */
        #[AutoWhenLoadedLazy]
        public Lazy|MusicTrackData $track,
        /** @var MusicSourceData $source */
        #[AutoWhenLoadedLazy]
        public Lazy|MusicSourceData $source,
        /** @var Collection<array-key,TrackAvailabilityData> $availabilities */
        #[AutoWhenLoadedLazy]
        public Collection|Lazy $availabilities,
    ) {}
}

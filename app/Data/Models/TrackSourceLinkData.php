<?php

declare(strict_types=1);

namespace App\Data\Models;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class TrackSourceLinkData extends Data
{
    public function __construct(
        public string $id,
        public string $track_id,
        public string $source_id,
        public string $external_id,
        public null|string $preview_url,
        public null|string $full_url,
        public null|string $embed_url,
        public null|string $album_art_url,
        public bool $is_verified,
        public bool $is_available,
        public null|Carbon $last_checked_at,
        public null|Carbon $created_at,
        public null|Carbon $updated_at,
        /** @var MusicTrackData|Optional $track */
        public Optional|MusicTrackData $track,
        /** @var MusicSourceData|Optional $source */
        public Optional|MusicSourceData $source,
        /** @var Collection<array-key,TrackAvailabilityData>|Optional */
        public Collection|Optional $availabilities,
    ) {}
}

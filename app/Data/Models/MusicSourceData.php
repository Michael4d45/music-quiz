<?php

declare(strict_types=1);

namespace App\Data\Models;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class MusicSourceData extends Data
{
    public function __construct(
        public string $id,
        public string $name,
        public string $display_name,
        public null|string $icon_url,
        public null|string $api_base_url,
        public bool $requires_authentication,
        public bool $is_active,
        public int $priority,
        public null|Carbon $created_at,
        public null|Carbon $updated_at,
        /** @var Collection<array-key,SourceApiCredentialData>|Optional */
        public Collection|Optional $api_credentials,
        /** @var Collection<array-key,MusicTrackData>|Optional */
        public Collection|Optional $primary_tracks,
        /** @var Collection<array-key,TrackSourceLinkData>|Optional */
        public Collection|Optional $track_source_links,
    ) {}
}

<?php

declare(strict_types=1);

namespace App\Data\Models;

use Illuminate\Support\Carbon;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class TrackAvailabilityData extends Data
{
    public function __construct(
        public string $id,
        public string $track_source_link_id,
        public null|string $country_code,
        public bool $is_available,
        public null|Carbon $last_checked_at,
        /** @var TrackSourceLinkData|Optional $track_source_link */
        public Optional|TrackSourceLinkData $track_source_link,
    ) {}
}

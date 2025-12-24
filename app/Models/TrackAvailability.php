<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * TrackAvailability model for regional availability of tracks.
 *
 * @property string $id
 * @property string $track_source_link_id
 * @property string|null $country_code
 * @property bool $is_available
 * @property Carbon|null $last_checked_at
 * @property-read TrackSourceLink $trackSourceLink
 */
class TrackAvailability extends Model
{
    /** @use HasFactory<\Database\Factories\TrackAvailabilityFactory> */
    use HasFactory;

    use HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<model-property<self>>
     */
    protected $fillable = [
        'track_source_link_id',
        'country_code',
        'is_available',
        'last_checked_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<model-property<self>, mixed>
     */
    #[\Override]
    protected function casts(): array
    {
        return [
            'is_available' => 'boolean',
            'last_checked_at' => 'datetime',
        ];
    }

    /**
     * Get the track source link that owns this availability record.
     *
     * @return BelongsTo<TrackSourceLink, $this>
     */
    public function trackSourceLink(): BelongsTo
    {
        return $this->belongsTo(TrackSourceLink::class);
    }
}

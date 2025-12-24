<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * TrackSourceLink model for linking tracks to streaming sources.
 *
 * @property string $id
 * @property string $track_id
 * @property string $source_id
 * @property string $external_id
 * @property string|null $preview_url
 * @property string|null $full_url
 * @property string|null $embed_url
 * @property string|null $album_art_url
 * @property bool $is_verified
 * @property bool $is_available
 * @property Carbon|null $last_checked_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $created_at
 * @property-read MusicTrack $track
 * @property-read MusicSource $source
 * @property-read Collection<array-key, TrackAvailability> $availabilities
 */
class TrackSourceLink extends Model
{
    /** @use HasFactory<\Database\Factories\TrackSourceLinkFactory> */
    use HasFactory;

    use HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<model-property<self>>
     */
    protected $fillable = [
        'track_id',
        'source_id',
        'external_id',
        'preview_url',
        'full_url',
        'embed_url',
        'album_art_url',
        'is_verified',
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
            'is_verified' => 'boolean',
            'is_available' => 'boolean',
            'last_checked_at' => 'datetime',
        ];
    }

    /**
     * Get the track that owns this link.
     *
     * @return BelongsTo<MusicTrack, $this>
     */
    public function track(): BelongsTo
    {
        return $this->belongsTo(MusicTrack::class, 'track_id');
    }

    /**
     * Get the source that owns this link.
     *
     * @return BelongsTo<MusicSource, $this>
     */
    public function source(): BelongsTo
    {
        return $this->belongsTo(MusicSource::class, 'source_id');
    }

    /**
     * Get all availability records for this link.
     *
     * @return HasMany<TrackAvailability, $this>
     */
    public function availabilities(): HasMany
    {
        return $this->hasMany(TrackAvailability::class, 'track_source_link_id');
    }
}

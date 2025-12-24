<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * MusicSource model for streaming platform integrations.
 *
 * @property string $id
 * @property string $name
 * @property string $display_name
 * @property string|null $icon_url
 * @property string|null $api_base_url
 * @property bool $requires_authentication
 * @property bool $is_active
 * @property int $priority
 * @property Carbon|null $updated_at
 * @property Carbon|null $created_at
 * @property-read Collection<array-key, SourceApiCredential> $apiCredentials
 * @property-read Collection<array-key, MusicTrack> $primaryTracks
 * @property-read Collection<array-key, TrackSourceLink> $trackSourceLinks
 */
class MusicSource extends Model
{
    /** @use HasFactory<\Database\Factories\MusicSourceFactory> */
    use HasFactory;

    use HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<model-property<self>>
     */
    protected $fillable = [
        'name',
        'display_name',
        'icon_url',
        'api_base_url',
        'requires_authentication',
        'is_active',
        'priority',
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
            'requires_authentication' => 'boolean',
            'is_active' => 'boolean',
            'priority' => 'integer',
        ];
    }

    /**
     * Get all API credentials for this source.
     *
     * @return HasMany<SourceApiCredential, $this>
     */
    public function apiCredentials(): HasMany
    {
        return $this->hasMany(SourceApiCredential::class, 'source_id');
    }

    /**
     * Get all tracks where this is the primary source.
     *
     * @return HasMany<MusicTrack, $this>
     */
    public function primaryTracks(): HasMany
    {
        return $this->hasMany(MusicTrack::class, 'primary_source_id');
    }

    /**
     * Get all track source links for this source.
     *
     * @return HasMany<TrackSourceLink, $this>
     */
    public function trackSourceLinks(): HasMany
    {
        return $this->hasMany(TrackSourceLink::class, 'source_id');
    }
}

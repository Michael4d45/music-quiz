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
 * MusicTrack model for storing music track information.
 *
 * @property string $id
 * @property string $title
 * @property string $artist_name
 * @property string|null $album_name
 * @property int|null $release_year
 * @property string|null $genre
 * @property int|null $duration_ms
 * @property string $sub_category_id
 * @property string $primary_source_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read SubCategory $subCategory
 * @property-read MusicSource $primarySource
 * @property-read Collection<array-key, TrackSourceLink> $sourceLinks
 * @property-read Collection<array-key, QuizQuestion> $quizQuestions
 */
class MusicTrack extends Model
{
    /** @use HasFactory<\Database\Factories\MusicTrackFactory> */
    use HasFactory;

    use HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<model-property<self>>
     */
    protected $fillable = [
        'title',
        'artist_name',
        'album_name',
        'release_year',
        'genre',
        'duration_ms',
        'sub_category_id',
        'primary_source_id',
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
            'release_year' => 'integer',
            'duration_ms' => 'integer',
        ];
    }

    /**
     * Get the sub-category that owns this track.
     *
     * @return BelongsTo<SubCategory, $this>
     */
    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(SubCategory::class);
    }

    /**
     * Get the primary source for this track.
     *
     * @return BelongsTo<MusicSource, $this>
     */
    public function primarySource(): BelongsTo
    {
        return $this->belongsTo(MusicSource::class, 'primary_source_id');
    }

    /**
     * Get all source links for this track.
     *
     * @return HasMany<TrackSourceLink, $this>
     */
    public function sourceLinks(): HasMany
    {
        return $this->hasMany(TrackSourceLink::class, 'track_id');
    }

    /**
     * Get all quiz questions for this track.
     *
     * @return HasMany<QuizQuestion, $this>
     */
    public function quizQuestions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class, 'track_id');
    }
}

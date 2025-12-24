<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * PlaylistItem model for linking questions to playlists.
 *
 * @property string $id
 * @property string $playlist_id
 * @property string $question_id
 * @property int $sort_order
 * @property Carbon $added_at
 * @property-read Playlist $playlist
 * @property-read QuizQuestion $question
 */
class PlaylistItem extends Model
{
    /** @use HasFactory<\Database\Factories\PlaylistItemFactory> */
    use HasFactory;

    use HasUuids;

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<model-property<self>>
     */
    protected $fillable = [
        'playlist_id',
        'question_id',
        'sort_order',
        'added_at',
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
            'sort_order' => 'integer',
            'added_at' => 'datetime',
        ];
    }

    /**
     * Get the playlist that owns this item.
     *
     * @return BelongsTo<Playlist, $this>
     */
    public function playlist(): BelongsTo
    {
        return $this->belongsTo(Playlist::class, 'playlist_id');
    }

    /**
     * Get the question that this item references.
     *
     * @return BelongsTo<QuizQuestion, $this>
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(QuizQuestion::class, 'question_id');
    }
}

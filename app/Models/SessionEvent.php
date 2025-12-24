<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EventType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * SessionEvent model for logging real-time events in game sessions.
 *
 * @property string $id
 * @property string $session_id
 * @property string|null $event_type
 * @property string|null $participant_id
 * @property array<string, mixed>|null $payload
 * @property Carbon|null $created_at
 * @property-read GameSession $session
 * @property-read SessionParticipant|null $participant
 */
class SessionEvent extends Model
{
    /** @use HasFactory<\Database\Factories\SessionEventFactory> */
    use HasFactory;

    use HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<model-property<self>>
     */
    protected $fillable = [
        'session_id',
        'event_type',
        'participant_id',
        'payload',
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
            'event_type' => EventType::class,
            'payload' => 'array',
        ];
    }

    /**
     * Get the session that owns this event.
     *
     * @return BelongsTo<GameSession, $this>
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(GameSession::class, 'session_id');
    }

    /**
     * Get the participant associated with this event.
     *
     * @return BelongsTo<SessionParticipant, $this>
     */
    public function participant(): BelongsTo
    {
        return $this->belongsTo(SessionParticipant::class, 'participant_id');
    }
}

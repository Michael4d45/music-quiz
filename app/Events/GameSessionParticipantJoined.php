<?php

declare(strict_types=1);

namespace App\Events;

use App\Data\Events\GameSessionParticipantJoinedData;
use App\Models\GameSession;
use App\Models\SessionParticipant;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameSessionParticipantJoined implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public GameSession $session,
        public SessionParticipant $participant,
        public int $participantCount,
    ) {}

    /**
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('game-session.' . $this->session->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'GameSessionParticipantJoined';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return GameSessionParticipantJoinedData::from([
            'session_id' => $this->session->id,
            'participant_id' => $this->participant->id,
            'participant_count' => $this->participantCount,
        ])->toArray();
    }
}

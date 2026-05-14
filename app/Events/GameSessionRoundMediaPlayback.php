<?php

declare(strict_types=1);

namespace App\Events;

use App\Data\Events\GameSessionRoundMediaPlaybackData;
use App\Models\GameSession;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameSessionRoundMediaPlayback implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public GameSession $session,
        public string $roundId,
        public bool $playing,
        public float $currentTimeSeconds,
        public int $serverSeq,
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
        return 'GameSessionRoundMediaPlayback';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return GameSessionRoundMediaPlaybackData::from([
            'session_id' => $this->session->id,
            'round_id' => $this->roundId,
            'playing' => $this->playing,
            'current_time_seconds' => $this->currentTimeSeconds,
            'server_seq' => $this->serverSeq,
        ])->toArray();
    }
}

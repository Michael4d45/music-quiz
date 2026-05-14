<?php

declare(strict_types=1);

namespace App\Events;

use App\Data\Events\GameSessionUpdatedData;
use App\Models\GameSession;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameSessionUpdated implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public GameSession $session,
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
        return 'GameSessionUpdated';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return GameSessionUpdatedData::from([
            'session_id' => $this->session->id,
        ])->toArray();
    }
}

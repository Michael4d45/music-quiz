<?php

declare(strict_types=1);

namespace App\Events;

use App\Data\Events\SessionEventOccurredData;
use App\Models\GameSession;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SessionEventOccurred implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     * @param array<string, mixed> $data
     */
    public function __construct(
        public GameSession $session,
        public string $event,
        public array $data = [],
        public int|string|null $userId = null,
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('session.' . $this->session->room_code),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return $this->event;
    }

    /**
     * Get the data to broadcast.
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return SessionEventOccurredData::from(array_merge($this->data, [
            'user_id' => $this->userId,
            'timestamp' => now(),
        ]))->toArray();
    }
}

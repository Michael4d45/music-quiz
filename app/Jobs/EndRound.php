<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Events\SessionEventOccurred;
use App\Models\SessionRound;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EndRound implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $roundId,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $round = SessionRound::find($this->roundId);

        if (!$round || $round->ended_at) {
            return;
        }

        $round->update(['ended_at' => now()]);

        $session = $round->session;

        broadcast(new SessionEventOccurred($session, 'RoundEnded', [
            'round_number' => $round->round_number,
        ]))->toOthers();
    }
}

<?php

declare(strict_types=1);

namespace App\Features\GameSessions\Actions;

use App\Enums\SessionStatus;
use App\Events\GameSessionUpdated;
use App\Models\GameSession;
use App\Support\GameSessions\GameSessionRoomViewBuilder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class AdvanceGameSessionRound
{
    public function __invoke(GameSession $gameSession): Response
    {
        $user = assertedUser();
        Gate::authorize('update', $gameSession);

        if ($gameSession->status !== SessionStatus::InProgress) {
            abort(422, 'The session is not in progress.');
        }

        DB::transaction(function () use ($gameSession): void {
            $current = $gameSession
                ->rounds()
                ->whereNotNull('started_at')
                ->whereNull('ended_at')
                ->orderBy('round_number')
                ->lockForUpdate()
                ->first();

            if ($current === null) {
                abort(422, 'There is no active round to advance.');
            }

            $current->update([
                'ended_at' => now(),
            ]);

            $next = $gameSession
                ->rounds()
                ->where('round_number', $current->round_number + 1)
                ->lockForUpdate()
                ->first();

            if ($next !== null) {
                $next->update([
                    'started_at' => now(),
                ]);

                return;
            }

            $gameSession->update([
                'status' => SessionStatus::Completed,
                'ended_at' => now(),
            ]);
        });

        $gameSession->refresh();
        $gameSession->load(StartGameSession::roomEagerLoads());

        event(new GameSessionUpdated($gameSession));

        return response()->json(GameSessionRoomViewBuilder::build(
            $gameSession,
            $user,
        ));
    }
}

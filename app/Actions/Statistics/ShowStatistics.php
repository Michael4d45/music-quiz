<?php

declare(strict_types=1);

namespace App\Actions\Statistics;

use App\Data\Models\GameSessionData;
use App\Data\Models\UserStatisticData;
use App\Data\Response\StatisticsResponse;
use App\Http\Requests\AuthRequest;
use App\Models\GameSession;
use App\Models\UserStatistic;
use Illuminate\Http\JsonResponse;

class ShowStatistics
{
    /**
     * Display the user's statistics.
     */
    public function __invoke(AuthRequest $request): JsonResponse
    {
        $user = $request->assertedUser();

        $statistic = UserStatistic::where('user_id', $user->id)->first();

        $recentSessions = GameSession::where('host_id', $user->id)
            ->with(['host', 'quizMode'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json(StatisticsResponse::from([
            'statistic' => $statistic
                ? UserStatisticData::from($statistic)
                : null,
            'recent_sessions' => GameSessionData::collect($recentSessions),
        ]));
    }
}

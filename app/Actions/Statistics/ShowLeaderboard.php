<?php

declare(strict_types=1);

namespace App\Actions\Statistics;

use App\Data\Models\UserStatisticData;
use App\Data\Response\LeaderboardResponse;
use App\Models\UserStatistic;
use Illuminate\Http\JsonResponse;

class ShowLeaderboard
{
    /**
     * Display the leaderboard data.
     */
    public function __invoke(): JsonResponse
    {
        $topPlayers = UserStatistic::with('user')
            ->orderBy('total_points', 'desc')
            ->limit(100)
            ->get();

        return response()->json(LeaderboardResponse::from([
            'players' => UserStatisticData::collect($topPlayers),
        ]));
    }
}

<?php

declare(strict_types=1);

namespace App\Actions\Home;

use App\Data\Models\GameSessionData;
use App\Data\Models\PlaylistData;
use App\Data\Models\UserStatisticData;
use App\Data\Response\HomeResponse;
use App\Models\GameSession;
use App\Models\Playlist;
use App\Models\UserStatistic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShowHome
{
    /**
     * Display the home view.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        // Treat guest users as non-authenticated for UI purposes
        if ($user === null || $user->is_guest) {
            return response()->json(HomeResponse::from([
                'statistic' => null,
                'recent_sessions' => [],
                'recent_playlists' => [],
            ]));
        }

        $statistic = UserStatistic::where('user_id', $user->id)->first();

        $recentSessions = GameSession::where('host_id', $user->id)
            ->with('host')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentPlaylists = Playlist::where('user_id', $user->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json(HomeResponse::from([
            'statistic' => $statistic
                ? UserStatisticData::from($statistic)
                : null,
            'recent_sessions' => GameSessionData::collect($recentSessions),
            'recent_playlists' => PlaylistData::collect($recentPlaylists),
        ]));
    }
}
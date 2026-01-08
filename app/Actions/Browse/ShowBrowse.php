<?php

declare(strict_types=1);

namespace App\Actions\Browse;

use App\Data\Models\CategoryData;
use App\Data\Models\MusicTrackData;
use App\Data\Models\PlaylistData;
use App\Data\Response\BrowseResponse;
use App\Models\Category;
use App\Models\MusicTrack;
use App\Models\Playlist;
use Illuminate\Http\JsonResponse;

class ShowBrowse
{
    /**
     * Display the browse data.
     */
    public function __invoke(): JsonResponse
    {
        $categories = Category::with('subCategories')->orderBy(
            'sort_order',
        )->get();

        $featuredPlaylists = Playlist::where('is_public', true)
            ->with('user')
            ->orderBy('play_count', 'desc')
            ->limit(6)
            ->get();

        $recentTracks = MusicTrack::with(['subCategory', 'primarySource'])
            ->orderBy('created_at', 'desc')
            ->limit(12)
            ->get();

        return response()->json(BrowseResponse::from([
            'categories' => CategoryData::collect($categories),
            'featured_playlists' => PlaylistData::collect($featuredPlaylists),
            'recent_tracks' => MusicTrackData::collect($recentTracks),
        ]));
    }
}

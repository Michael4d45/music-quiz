<?php

declare(strict_types=1);

namespace App\Features\MusicTracks\Actions;

use App\Data\Models\MusicTrackData;
use App\Features\MusicTracks\Requests\StoreMusicTrackUploadRequest;
use App\Models\MusicSource;
use App\Models\MusicTrack;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class CreateMusicTrackUpload
{
    public function __invoke(StoreMusicTrackUploadRequest $request): Response
    {
        Gate::authorize('create', MusicTrack::class);

        $user = assertedUser();

        $uploadSource = MusicSource::query()
            ->where('name', 'user_upload')
            ->firstOrFail();

        $file = $request->audio;
        $storedPath = $file->store("music-track-uploads/{$user->id}", 'local');

        if ($storedPath === false) {
            throw new \RuntimeException('Failed to store audio file.');
        }

        try {
            $track = MusicTrack::query()->create([
                'user_id' => $user->id,
                'title' => $request->title,
                'artist_name' => $request->artist_name,
                'album_name' => $request->album_name,
                'release_year' => $request->release_year,
                'genre' => $request->genre,
                'duration_ms' => $request->duration_ms,
                'origin_kind' => $request->origin_kind,
                'origin_title' => $request->origin_title,
                'user_upload_path' => $storedPath,
                'user_upload_original_name' => $file->getClientOriginalName(),
                'sub_category_id' => $request->sub_category_id,
                'primary_source_id' => $uploadSource->id,
            ]);
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($storedPath);

            throw $exception;
        }

        $track->load(['subCategory', 'primarySource']);

        return response()->json(MusicTrackData::from($track), 201);
    }
}

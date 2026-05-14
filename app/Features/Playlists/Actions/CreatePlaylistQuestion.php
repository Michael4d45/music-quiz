<?php

declare(strict_types=1);

namespace App\Features\Playlists\Actions;

use App\Enums\Visibility;
use App\Features\QuizQuestions\Requests\StoreQuizQuestionRequest;
use App\Models\MusicTrack;
use App\Models\Playlist;
use App\Models\PlaylistItem;
use App\Models\QuizQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class CreatePlaylistQuestion
{
    public function __invoke(Request $request, Playlist $playlist): Response
    {
        Gate::authorize('update', $playlist);

        $user = assertedUser();
        $validatedResult = StoreQuizQuestionRequest::validate($request->only([
            'question_type',
            'correct_answer',
            'base_points',
            'difficulty_level',
            'track_id',
            'prompt_text',
            'media_start_seconds',
            'media_end_seconds',
            'visibility',
        ]));
        $validated = is_array($validatedResult)
            ? $validatedResult
            : $validatedResult->toArray();
        $dto = StoreQuizQuestionRequest::from($validated);

        if ($dto->track_id !== null) {
            $track = MusicTrack::query()->findOrFail($dto->track_id);
            Gate::authorize('view', $track);
        }

        DB::transaction(function () use ($user, $playlist, $dto): void {
            $question = QuizQuestion::query()->create([
                'user_id' => $user->id,
                'track_id' => $dto->track_id,
                'question_type' => $dto->question_type,
                'prompt_text' => $dto->prompt_text,
                'correct_answer' => $dto->correct_answer,
                'base_points' => $dto->base_points,
                'media_start_seconds' => $dto->media_start_seconds,
                'media_end_seconds' => $dto->media_end_seconds,
                'difficulty_level' => $dto->difficulty_level,
                'visibility' => $dto->visibility ?? Visibility::Private,
            ]);

            $maxSort = PlaylistItem::query()->where(
                'playlist_id',
                $playlist->id,
            )->max('sort_order');

            $sortOrder = is_numeric($maxSort) ? (int) $maxSort + 100 : 100;

            PlaylistItem::query()->create([
                'playlist_id' => $playlist->id,
                'question_id' => $question->id,
                'sort_order' => $sortOrder,
                'added_at' => now(),
            ]);
        });

        return app(ListPlaylistItems::class)($playlist);
    }
}

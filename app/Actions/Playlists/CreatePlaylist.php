<?php

declare(strict_types=1);

namespace App\Actions\Playlists;

use App\Data\Models\PlaylistData;
use App\Data\Requests\CreatePlaylistRequest;
use App\Data\Response\PlaylistResponse;
use App\Enums\Visibility;
use App\Http\Requests\AuthRequest;
use App\Models\Playlist;
use App\Models\PlaylistItem;
use App\Models\QuizQuestion;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class CreatePlaylist
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(
        CreatePlaylistRequest $data,
        AuthRequest $request,
    ): JsonResponse {
        $user = $request->assertedUser();

        $playlist = DB::transaction(function () use ($data, $user) {
            $playlist = Playlist::create([
                'user_id' => $user->id,
                'name' => $data->name,
                'description' => $data->description,
                'is_public' => $data->is_public,
                'play_count' => 0,
            ]);

            // Create new questions first
            $newQuestionIds = [];
            foreach ($data->new_questions as $questionData) {
                $question = QuizQuestion::create([
                    'user_id' => $user->id,
                    'track_id' => $questionData->track_id,
                    'question_type' => $questionData->question_type,
                    'prompt_text' => $questionData->prompt_text,
                    'correct_answer' => $questionData->correct_answer,
                    'base_points' => $questionData->base_points,
                    'media_start_seconds' => $questionData->media_start_seconds,
                    'media_end_seconds' => $questionData->media_end_seconds,
                    'difficulty_level' => $questionData->difficulty_level,
                    'visibility' => Visibility::Public,
                ]);
                $newQuestionIds[] = $question->id;
            }

            // Combine existing question IDs with newly created ones
            $allQuestionIds = array_merge($data->question_ids, $newQuestionIds);

            // Add all questions to playlist
            foreach ($allQuestionIds as $index => $questionId) {
                PlaylistItem::create([
                    'playlist_id' => $playlist->id,
                    'question_id' => $questionId,
                    'sort_order' => $index + 1,
                    'added_at' => now(),
                ]);
            }

            return $playlist;
        });

        $playlist->load(['user', 'items.question']);

        return response()->json(PlaylistResponse::from([
            'playlist' => PlaylistData::from($playlist),
        ]), 201);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature\Playlists;

use App\Enums\QuestionType;
use App\Models\MusicTrack;
use App\Models\Playlist;
use App\Models\PlaylistItem;
use App\Models\QuizQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreatePlaylistQuestionApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_question_and_appends_playlist_item(): void
    {
        $user = User::factory()->create();
        $playlist = Playlist::factory()->for($user)->create();

        $this->actingAs($user, 'web')
            ->postJson("/api/my/playlists/{$playlist->id}/questions", [
                'question_type' => QuestionType::Artist->value,
                'correct_answer' => 'The Answer',
                'base_points' => 500,
                'difficulty_level' => 3,
                'prompt_text' => 'Who is this?',
                'track_id' => null,
            ])
            ->assertOk()
            ->assertJsonPath('playlist.id', $playlist->id)
            ->assertJsonCount(1, 'items');

        $this->assertDatabaseCount('quiz_questions', 1);
        $this->assertDatabaseHas('quiz_questions', [
            'user_id' => $user->id,
            'correct_answer' => 'The Answer',
        ]);
        $this->assertDatabaseCount('playlist_items', 1);
        $questionId = QuizQuestion::query()->value('id');
        $this->assertIsString($questionId);
        $this->assertDatabaseHas('playlist_items', [
            'playlist_id' => $playlist->id,
            'question_id' => $questionId,
            'sort_order' => 100,
        ]);
    }

    public function test_appends_after_existing_items(): void
    {
        $user = User::factory()->create();
        $playlist = Playlist::factory()->for($user)->create();
        $existing = QuizQuestion::factory()->for($user)->create();
        PlaylistItem::factory()->create([
            'playlist_id' => $playlist->id,
            'question_id' => $existing->id,
            'sort_order' => 100,
        ]);

        $this->actingAs($user, 'web')
            ->postJson("/api/my/playlists/{$playlist->id}/questions", [
                'question_type' => QuestionType::Title->value,
                'correct_answer' => 'Song title',
                'base_points' => 1000,
                'difficulty_level' => 2,
                'track_id' => null,
            ])
            ->assertOk()
            ->assertJsonCount(2, 'items');

        $this->assertDatabaseHas('playlist_items', [
            'playlist_id' => $playlist->id,
            'question_id' => $existing->id,
            'sort_order' => 100,
        ]);

        $newQuestionId = QuizQuestion::query()
            ->where('id', '!=', $existing->id)
            ->value('id');
        $this->assertIsString($newQuestionId);
        $this->assertDatabaseHas('playlist_items', [
            'playlist_id' => $playlist->id,
            'question_id' => $newQuestionId,
            'sort_order' => 200,
        ]);
    }

    public function test_validates_like_standalone_question_create(): void
    {
        $user = User::factory()->create();
        $playlist = Playlist::factory()->for($user)->create();

        $this->actingAs($user, 'web')
            ->postJson("/api/my/playlists/{$playlist->id}/questions", [
                'question_type' => QuestionType::Artist->value,
                'correct_answer' => '',
                'base_points' => 1000,
                'difficulty_level' => 2,
            ])
            ->assertStatus(422);
    }

    public function test_links_track_when_owned(): void
    {
        $user = User::factory()->create();
        $playlist = Playlist::factory()->for($user)->create();
        $track = MusicTrack::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user, 'web')
            ->postJson("/api/my/playlists/{$playlist->id}/questions", [
                'question_type' => QuestionType::AudioClip->value,
                'correct_answer' => 'Clip answer',
                'base_points' => 800,
                'difficulty_level' => 4,
                'track_id' => $track->id,
            ])
            ->assertOk();

        $this->assertDatabaseHas('quiz_questions', [
            'user_id' => $user->id,
            'track_id' => $track->id,
        ]);
    }
}

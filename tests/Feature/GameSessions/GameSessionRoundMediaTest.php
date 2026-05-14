<?php

declare(strict_types=1);

use App\Enums\SessionStatus;
use App\Events\GameSessionRoundMediaPlayback;
use App\Models\GameSession;
use App\Models\MusicTrack;
use App\Models\QuizQuestion;
use App\Models\SessionParticipant;
use App\Models\SessionRound;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;

test('participant may stream round question audio when track has an upload', function (): void {
    Storage::disk('local')->put('test-game-audio/clip.mp3', 'fake-audio');

    $host = User::factory()->create();
    $player = User::factory()->create();

    $track = MusicTrack::factory()->create([
        'user_id' => $host->id,
        'user_upload_path' => 'test-game-audio/clip.mp3',
        'user_upload_original_name' => 'clip.mp3',
    ]);

    $question = QuizQuestion::factory()->create([
        'user_id' => $host->id,
        'track_id' => $track->id,
    ]);

    $session = GameSession::factory()->create([
        'host_id' => $host->id,
        'status' => SessionStatus::InProgress,
        'started_at' => now(),
    ]);

    SessionParticipant::factory()->create([
        'session_id' => $session->id,
        'user_id' => $player->id,
    ]);

    $round = SessionRound::factory()->create([
        'session_id' => $session->id,
        'question_id' => $question->id,
        'round_number' => 1,
        'started_at' => now(),
        'ended_at' => null,
    ]);

    $this
        ->actingAs($player, 'web')
        ->get(
            '/api/game-sessions/' . $session->id . '/rounds/' . $round->id . '/audio',
        )
        ->assertSuccessful();
});

test('non participant cannot stream round question audio', function (): void {
    Storage::disk('local')->put('test-game-audio/clip2.mp3', 'fake-audio');

    $host = User::factory()->create();
    $stranger = User::factory()->create();

    $track = MusicTrack::factory()->create([
        'user_id' => $host->id,
        'user_upload_path' => 'test-game-audio/clip2.mp3',
        'user_upload_original_name' => 'clip2.mp3',
    ]);

    $question = QuizQuestion::factory()->create([
        'user_id' => $host->id,
        'track_id' => $track->id,
    ]);

    $session = GameSession::factory()->create([
        'host_id' => $host->id,
        'status' => SessionStatus::InProgress,
        'started_at' => now(),
    ]);

    $round = SessionRound::factory()->create([
        'session_id' => $session->id,
        'question_id' => $question->id,
        'round_number' => 1,
        'started_at' => now(),
        'ended_at' => null,
    ]);

    $this
        ->actingAs($stranger, 'web')
        ->get(
            '/api/game-sessions/' . $session->id . '/rounds/' . $round->id . '/audio',
        )
        ->assertForbidden();
});

test('host can broadcast media playback sync and participant cannot', function (): void {
    Event::fake([GameSessionRoundMediaPlayback::class]);

    $host = User::factory()->create();
    $player = User::factory()->create();

    $session = GameSession::factory()->create([
        'host_id' => $host->id,
        'status' => SessionStatus::InProgress,
        'started_at' => now(),
    ]);

    SessionParticipant::factory()->create([
        'session_id' => $session->id,
        'user_id' => $player->id,
    ]);

    $question = QuizQuestion::factory()->create([
        'user_id' => $host->id,
    ]);

    $round = SessionRound::factory()->create([
        'session_id' => $session->id,
        'question_id' => $question->id,
        'round_number' => 1,
        'started_at' => now(),
        'ended_at' => null,
    ]);

    $this
        ->actingAs($host, 'web')
        ->postJson(
            '/api/game-sessions/' . $session->id . '/rounds/' . $round->id . '/media-playback',
            [
                'playing' => true,
                'current_time_seconds' => 1.25,
            ],
        )
        ->assertNoContent();

    Event::assertDispatched(GameSessionRoundMediaPlayback::class);

    $this
        ->actingAs($player, 'web')
        ->postJson(
            '/api/game-sessions/' . $session->id . '/rounds/' . $round->id . '/media-playback',
            [
                'playing' => false,
                'current_time_seconds' => 0,
            ],
        )
        ->assertForbidden();
});

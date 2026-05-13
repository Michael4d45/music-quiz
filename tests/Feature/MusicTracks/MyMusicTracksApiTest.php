<?php

declare(strict_types=1);

namespace Tests\Feature\MusicTracks;

use App\Enums\MusicTrackOriginKind;
use App\Models\MusicSource;
use App\Models\SubCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MyMusicTracksApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_json_create_rejects_user_upload_primary_source(): void
    {
        $user = User::factory()->create();
        $sub = SubCategory::factory()->create();
        $uploadSource = MusicSource::query()
            ->where('name', 'user_upload')
            ->firstOrFail();

        $this
            ->actingAs($user, 'web')
            ->postJson('/api/my/music-tracks', [
                'title' => 'Blocked',
                'artist_name' => 'Artist',
                'sub_category_id' => $sub->id,
                'primary_source_id' => $uploadSource->id,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['primary_source_id']);
    }

    public function test_registered_user_can_create_streaming_track_with_origin_metadata(): void
    {
        $user = User::factory()->create();
        $sub = SubCategory::factory()->create();
        $streaming = MusicSource::factory()->create(['name' => 'test_stream']);

        $response = $this->actingAs(
            $user,
            'web',
        )->postJson('/api/my/music-tracks', [
            'title' => 'Still Alive',
            'artist_name' => 'Jonathan Coulton',
            'sub_category_id' => $sub->id,
            'primary_source_id' => $streaming->id,
            'origin_kind' => MusicTrackOriginKind::SoundtrackGame->value,
            'origin_title' => 'Portal',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('origin_title', 'Portal');
        $response->assertJsonPath(
            'origin_kind',
            MusicTrackOriginKind::SoundtrackGame->value,
        );

        $this->assertDatabaseHas('music_tracks', [
            'user_id' => $user->id,
            'title' => 'Still Alive',
            'origin_title' => 'Portal',
        ]);
    }

    public function test_registered_user_can_upload_audio_track(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $sub = SubCategory::factory()->create();

        MusicSource::query()->where('name', 'user_upload')->firstOrFail();

        $file = UploadedFile::fake()->create('clip.mp3', 128, 'audio/mpeg');

        $response = $this->actingAs($user, 'web')->post(
            '/api/my/music-tracks/upload',
            [
                'title' => 'Custom clip',
                'artist_name' => 'Me',
                'sub_category_id' => $sub->id,
                'audio' => $file,
            ],
            ['Accept' => 'application/json'],
        );

        $response->assertCreated();
        $response->assertJsonPath('title', 'Custom clip');
        $path = $response->json('user_upload_path');
        static::assertIsString($path);
        Storage::disk('local')->assertExists($path);
    }

    public function test_delete_removes_uploaded_audio_file(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $sub = SubCategory::factory()->create();
        MusicSource::query()->where('name', 'user_upload')->firstOrFail();

        $file = UploadedFile::fake()->create('gone.mp3', 64, 'audio/mpeg');

        $create = $this->actingAs($user, 'web')->post(
            '/api/my/music-tracks/upload',
            [
                'title' => 'Temp',
                'artist_name' => 'Temp',
                'sub_category_id' => $sub->id,
                'audio' => $file,
            ],
            ['Accept' => 'application/json'],
        );

        $create->assertCreated();
        $id = $create->json('id');
        $path = $create->json('user_upload_path');
        static::assertIsString($path);

        $this
            ->actingAs($user, 'web')
            ->deleteJson("/api/my/music-tracks/{$id}")
            ->assertOk();

        Storage::disk('local')->assertMissing($path);
        $this->assertDatabaseMissing('music_tracks', ['id' => $id]);
    }

    public function test_guest_cannot_upload_tracks(): void
    {
        $guest = User::factory()->guest()->create();
        $sub = SubCategory::factory()->create();
        $file = UploadedFile::fake()->create('nope.mp3', 32, 'audio/mpeg');

        $this
            ->actingAs($guest, 'web')
            ->post(
                '/api/my/music-tracks/upload',
                [
                    'title' => 'X',
                    'artist_name' => 'Y',
                    'sub_category_id' => $sub->id,
                    'audio' => $file,
                ],
                ['Accept' => 'application/json'],
            )
            ->assertForbidden();
    }
}

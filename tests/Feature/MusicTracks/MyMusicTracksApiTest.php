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

    public function test_owner_can_stream_uploaded_track_audio(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $sub = SubCategory::factory()->create();
        MusicSource::query()->where('name', 'user_upload')->firstOrFail();

        $file = UploadedFile::fake()->create('stream-me.mp3', 64, 'audio/mpeg');

        $create = $this->actingAs($user, 'web')->post(
            '/api/my/music-tracks/upload',
            [
                'title' => 'Stream test',
                'artist_name' => 'Tester',
                'sub_category_id' => $sub->id,
                'audio' => $file,
            ],
            ['Accept' => 'application/json'],
        );

        $create->assertCreated();
        $id = $create->json('id');

        $stream = $this->actingAs($user, 'web')->get(
            "/api/my/music-tracks/{$id}/audio",
        );

        $stream->assertOk();
        $stream->assertHeader('Content-Type', 'audio/mpeg');
        $disposition = (string) $stream->headers->get('Content-Disposition');
        static::assertStringContainsStringIgnoringCase('inline', $disposition);
    }

    public function test_stream_returns_404_when_track_has_no_upload(): void
    {
        $user = User::factory()->create();
        $sub = SubCategory::factory()->create();
        $streaming = MusicSource::factory()->create(['name' => 'catalog_x']);

        $create = $this->actingAs(
            $user,
            'web',
        )->postJson('/api/my/music-tracks', [
            'title' => 'Catalog only',
            'artist_name' => 'Band',
            'sub_category_id' => $sub->id,
            'primary_source_id' => $streaming->id,
        ]);

        $create->assertCreated();
        $id = $create->json('id');

        $this
            ->actingAs($user, 'web')
            ->get("/api/my/music-tracks/{$id}/audio")
            ->assertNotFound();
    }

    public function test_other_user_cannot_stream_uploaded_audio(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $sub = SubCategory::factory()->create();
        MusicSource::query()->where('name', 'user_upload')->firstOrFail();

        $file = UploadedFile::fake()->create('private.mp3', 32, 'audio/mpeg');

        $create = $this->actingAs($owner, 'web')->post(
            '/api/my/music-tracks/upload',
            [
                'title' => 'Mine',
                'artist_name' => 'Me',
                'sub_category_id' => $sub->id,
                'audio' => $file,
            ],
            ['Accept' => 'application/json'],
        );

        $create->assertCreated();
        $id = $create->json('id');

        $this
            ->actingAs($stranger, 'web')
            ->get("/api/my/music-tracks/{$id}/audio")
            ->assertForbidden();
    }
}

<?php

declare(strict_types=1);

namespace App\Features\MusicTracks\Requests;

use App\Enums\MusicTrackOriginKind;
use App\Models\MusicSource;
use App\Models\MusicTrack;
use Illuminate\Validation\Validator;
use Spatie\LaravelData\Attributes\Validation\Enum as EnumRule;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Sometimes;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\Uuid;
use Spatie\LaravelData\Data;

class UpdateMusicTrackRequest extends Data
{
    public function __construct(
        #[Sometimes, StringType, Max(255)]
        public null|string $title = null,
        #[Sometimes, StringType, Max(255)]
        public null|string $artist_name = null,
        #[Sometimes, Nullable, StringType, Max(255)]
        public null|string $album_name = null,
        #[Sometimes, Nullable, IntegerType, Min(1800), Max(2100)]
        public null|int $release_year = null,
        #[Sometimes, Nullable, StringType, Max(120)]
        public null|string $genre = null,
        #[Sometimes, Nullable, IntegerType, Min(0)]
        public null|int $duration_ms = null,
        #[Sometimes, Uuid, Exists('sub_categories', 'id')]
        public null|string $sub_category_id = null,
        #[Sometimes, Uuid, Exists('music_sources', 'id')]
        public null|string $primary_source_id = null,
        #[Sometimes, Nullable, EnumRule(MusicTrackOriginKind::class)]
        public null|MusicTrackOriginKind $origin_kind = null,
        #[Sometimes, Nullable, StringType, Max(255)]
        public null|string $origin_title = null,
    ) {}

    public static function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $uploadId = MusicSource::query()
                ->where('name', 'user_upload')
                ->value('id');

            if (!is_string($uploadId)) {
                return;
            }

            $incoming = $validator->getData()['primary_source_id'] ?? null;
            if (!is_string($incoming)) {
                return;
            }

            if ($incoming !== $uploadId) {
                return;
            }

            $track = request()->route('musicTrack');
            if (
                $track instanceof MusicTrack
                && $track->primary_source_id === $uploadId
            ) {
                return;
            }

            $validator->errors()->add(
                'primary_source_id',
                'Choose a streaming catalog, or add a new file-based track from the library page.',
            );
        });
    }
}

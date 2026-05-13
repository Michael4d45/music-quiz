<?php

declare(strict_types=1);

namespace App\Features\MusicTracks\Requests;

use App\Enums\MusicTrackOriginKind;
use Illuminate\Http\UploadedFile;
use Spatie\LaravelData\Attributes\Validation\Enum as EnumRule;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\File;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\Uuid;
use Spatie\LaravelData\Data;

class StoreMusicTrackUploadRequest extends Data
{
    public function __construct(
        #[Required, StringType, Max(255)]
        public string $title,
        #[Required, StringType, Max(255)]
        public string $artist_name,
        #[Required, Uuid, Exists('sub_categories', 'id')]
        public string $sub_category_id,
        #[Required, File, Max(51_200)]
        #[Rule(
            'mimetypes:audio/mpeg,audio/mp3,audio/wav,audio/x-wav,audio/mp4,audio/x-m4a,audio/flac,audio/ogg,audio/x-flac',
        )]
        public UploadedFile $audio,
        #[Nullable, StringType, Max(255)]
        public null|string $album_name = null,
        #[Nullable, IntegerType, Min(1800), Max(2100)]
        public null|int $release_year = null,
        #[Nullable, StringType, Max(120)]
        public null|string $genre = null,
        #[Nullable, IntegerType, Min(0)]
        public null|int $duration_ms = null,
        #[Nullable, EnumRule(MusicTrackOriginKind::class)]
        public null|MusicTrackOriginKind $origin_kind = null,
        #[Nullable, StringType, Max(255)]
        public null|string $origin_title = null,
    ) {}
}

<?php

declare(strict_types=1);

namespace App\Data\Models;

use App\Enums\CredentialType;
use Illuminate\Support\Carbon;
use Spatie\LaravelData\Attributes\AutoWhenLoadedLazy;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

class SourceApiCredentialData extends Data
{
    public function __construct(
        public string $id,
        public string $source_id,
        public CredentialType|null $credential_type,
        public string $encrypted_value,
        public Carbon|null $expires_at,
        public Carbon|null $created_at,
        public Carbon|null $updated_at,
        /** @var MusicSourceData $source */
        #[AutoWhenLoadedLazy]
        public Lazy|MusicSourceData $source,
    ) {}
}

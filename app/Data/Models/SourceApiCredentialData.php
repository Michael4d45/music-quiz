<?php

declare(strict_types=1);

namespace App\Data\Models;

use App\Enums\CredentialType;
use Illuminate\Support\Carbon;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class SourceApiCredentialData extends Data
{
    public function __construct(
        public string $id,
        public string $source_id,
        public null|CredentialType $credential_type,
        public string $encrypted_value,
        public null|Carbon $expires_at,
        public null|Carbon $created_at,
        public null|Carbon $updated_at,
        /** @var MusicSourceData|Optional $source */
        public Optional|MusicSourceData $source,
    ) {}
}

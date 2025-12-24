<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CredentialType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * SourceApiCredential model for storing encrypted API credentials.
 *
 * @property string $id
 * @property string $source_id
 * @property CredentialType|null $credential_type
 * @property string $encrypted_value
 * @property Carbon|null $expires_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read MusicSource $source
 */
class SourceApiCredential extends Model
{
    /** @use HasFactory<\Database\Factories\SourceApiCredentialFactory> */
    use HasFactory;

    use HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<model-property<self>>
     */
    protected $fillable = [
        'source_id',
        'credential_type',
        'encrypted_value',
        'expires_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<model-property<self>>
     */
    protected $hidden = [
        'encrypted_value',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<model-property<self>, mixed>
     */
    #[\Override]
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'credential_type' => CredentialType::class,
        ];
    }

    /**
     * Get the music source that owns this credential.
     *
     * @return BelongsTo<MusicSource, $this>
     */
    public function source(): BelongsTo
    {
        return $this->belongsTo(MusicSource::class, 'source_id');
    }
}

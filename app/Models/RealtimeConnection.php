<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RealtimeConnection extends Model
{
    protected $fillable = [
        'socket_id',
        'user_id',
        'channel_name',
        'ip_address',
        'user_agent',
        'connected_at',
        'disconnected_at',
    ];

    protected $casts = [
        'user_agent' => 'array',
        'connected_at' => 'datetime',
        'disconnected_at' => 'datetime',
    ];

    /**
     * Get the user that owns the connection
     *
     * @return BelongsTo<User,$this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for active connections
     *
     * @param Builder<RealtimeConnection> $query
     * @return Builder<RealtimeConnection>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('disconnected_at');
    }

    /**
     * Scope for connections by channel
     *
     * @param Builder<RealtimeConnection> $query
     * @return Builder<RealtimeConnection>
     */
    public function scopeByChannel(Builder $query, string $channelName): Builder
    {
        return $query->where('channel_name', $channelName);
    }
}

<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\GameSession;
use RuntimeException;

final class RoomCodeGenerator
{
    private const int MAX_ATTEMPTS = 40;

    /**
     * Generate a unique 6-character room code (same shape as {@see GameSessionFactory}).
     */
    public static function generate(): string
    {
        for ($i = 0; $i < self::MAX_ATTEMPTS; $i++) {
            $code = strtoupper((string) fake()->bothify('???###'));

            if (!GameSession::query()->where('room_code', $code)->exists()) {
                return $code;
            }
        }

        throw new RuntimeException('Unable to generate a unique room code.');
    }
}

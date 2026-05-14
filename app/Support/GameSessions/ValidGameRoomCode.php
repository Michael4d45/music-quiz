<?php

declare(strict_types=1);

namespace App\Support\GameSessions;

final class ValidGameRoomCode
{
    public static function invalidFormatMessage(): string
    {
        return 'Room codes must be exactly 6 letters or numbers (A–Z, 0–9).';
    }

    public static function isValid(string $roomCode): bool
    {
        return (bool) preg_match('/^[A-Za-z0-9]{6}$/', trim($roomCode));
    }

    public static function normalize(string $roomCode): string
    {
        return strtoupper(trim($roomCode));
    }
}

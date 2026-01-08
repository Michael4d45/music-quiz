<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum SessionStatus: string implements HasColor, HasLabel
{
    use EnumUtil;

    case Lobby = 'lobby';
    case InProgress = 'in_progress';
    case RoundTransition = 'round_transition';
    case Paused = 'paused';
    case Completed = 'completed';

    public function getColor(): string
    {
        return match ($this) {
            self::Lobby => 'gray',
            self::InProgress => 'info',
            self::RoundTransition => 'warning',
            self::Paused => 'danger',
            self::Completed => 'success',
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::Lobby => 'Lobby',
            self::InProgress => 'In Progress',
            self::RoundTransition => 'Round Transition',
            self::Paused => 'Paused',
            self::Completed => 'Completed',
        };
    }
}

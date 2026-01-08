<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum Role: string implements HasColor, HasLabel
{
    use EnumUtil;

    case Host = 'host';
    case Player = 'player';
    case Spectator = 'spectator';

    public function getColor(): string
    {
        return match ($this) {
            self::Host => 'primary',
            self::Player => 'success',
            self::Spectator => 'gray',
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::Host => 'Host',
            self::Player => 'Player',
            self::Spectator => 'Spectator',
        };
    }
}

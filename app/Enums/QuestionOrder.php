<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum QuestionOrder: string implements HasColor, HasLabel
{
    use EnumUtil;

    case Fixed = 'fixed';
    case Random = 'random';

    public function getColor(): string
    {
        return match ($this) {
            self::Fixed => 'primary',
            self::Random => 'secondary',
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::Fixed => 'Fixed',
            self::Random => 'Random',
        };
    }
}
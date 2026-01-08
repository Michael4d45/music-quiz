<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum EventType: string implements HasColor, HasLabel
{
    use EnumUtil;

    case PlayerJoin = 'player_join';
    case PlayerLeave = 'player_leave';
    case AnswerSubmitted = 'answer_submitted';
    case BuzzIn = 'buzz_in';
    case TimerExpired = 'timer_expired';
    case HostOverride = 'host_override';
    case RoundStart = 'round_start';
    case RoundEnd = 'round_end';

    public function getColor(): string
    {
        return match ($this) {
            self::PlayerJoin => 'success',
            self::PlayerLeave => 'danger',
            self::AnswerSubmitted => 'info',
            self::BuzzIn => 'warning',
            self::TimerExpired => 'gray',
            self::HostOverride => 'danger',
            self::RoundStart => 'info',
            self::RoundEnd => 'success',
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::PlayerJoin => 'Player Join',
            self::PlayerLeave => 'Player Leave',
            self::AnswerSubmitted => 'Answer Submitted',
            self::BuzzIn => 'Buzz In',
            self::TimerExpired => 'Timer Expired',
            self::HostOverride => 'Host Override',
            self::RoundStart => 'Round Start',
            self::RoundEnd => 'Round End',
        };
    }
}

<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum QuestionType: string implements HasColor, HasLabel
{
    use EnumUtil;

    case Artist = 'artist';
    case Title = 'title';
    case Year = 'year';
    case MultipleChoice = 'multiple_choice';
    case Lyric = 'lyric';
    case AudioClip = 'audio_clip';
    case TextInput = 'text_input';
    case TrueFalse = 'true_false';

    public function getColor(): string
    {
        return match ($this) {
            self::Artist => 'gray',
            self::Title => 'info',
            self::Year => 'warning',
            self::MultipleChoice => 'success',
            self::Lyric => 'danger',
            self::AudioClip => 'gray',
            self::TextInput => 'primary',
            self::TrueFalse => 'secondary',
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::Artist => 'Artist',
            self::Title => 'Title',
            self::Year => 'Year',
            self::MultipleChoice => 'Multiple Choice',
            self::Lyric => 'Lyric',
            self::AudioClip => 'Audio Clip',
            self::TextInput => 'Text Input',
            self::TrueFalse => 'True/False',
        };
    }
}

<?php

declare(strict_types=1);

namespace App\Enums;

enum QuizMode: string
{
    use EnumUtil;

    case MultipleChoice = 'multiple_choice';
    case TypeIn = 'type_in';
    case BuzzIn = 'buzz_in';
    case HostJudged = 'host_judged';
}

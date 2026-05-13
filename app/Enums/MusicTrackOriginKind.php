<?php

declare(strict_types=1);

namespace App\Enums;

enum MusicTrackOriginKind: string
{
    case Album = 'album';
    case SoundtrackGame = 'soundtrack_game';
    case SoundtrackFilm = 'soundtrack_film';
    case SoundtrackTv = 'soundtrack_tv';
    case OtherMedia = 'other_media';
}

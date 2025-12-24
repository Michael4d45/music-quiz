<?php

declare(strict_types=1);

namespace App\Enums;

enum MusicSource: string
{
    use EnumUtil;

    case Spotify = 'spotify';
    case Youtube = 'youtube';
    case Soundcloud = 'soundcloud';
    case Bandcamp = 'bandcamp';
    case AppleMusic = 'apple_music';
    case Deezer = 'deezer';
}

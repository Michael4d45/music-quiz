<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\MusicTrack;
use App\Models\QuizQuestion;
use Filament\Widgets\Widget;

class MusicTrackPlayerWidget extends Widget
{
    protected string $view = 'filament.widgets.music-track-player-widget';

    protected array|int|string $columnSpan = 'full';

    public MusicTrack|null $track = null;

    public QuizQuestion|null $question = null;

    protected static bool $isLazy = false;

    public function mount(MusicTrack|QuizQuestion|null $record = null): void
    {
        if ($record instanceof MusicTrack) {
            $this->track = $record->load([
                'primarySource',
                'sourceLinks.source',
            ]);
        } elseif ($record instanceof QuizQuestion) {
            $this->question = $record->load([
                'track.primarySource',
                'track.sourceLinks.source',
            ]);
            $this->track = $this->question->track;
        }
    }
}

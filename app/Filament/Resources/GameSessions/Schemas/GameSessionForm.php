<?php

declare(strict_types=1);

namespace App\Filament\Resources\GameSessions\Schemas;

use App\Enums\SessionStatus;
use App\Models\Playlist;
use App\Models\QuizMode;
use App\Models\ScoringRule;
use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class GameSessionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('host_id')
                    ->label('Host')
                    ->options(User::pluck('name', 'id'))
                    ->required()
                    ->searchable(),

                TextInput::make('room_code')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                Select::make('status')
                    ->options(SessionStatus::class)
                    ->required()
                    ->default(SessionStatus::Lobby),

                Select::make('quiz_mode_id')
                    ->label('Quiz Mode')
                    ->options(QuizMode::pluck('name', 'id'))
                    ->required()
                    ->searchable(),

                Select::make('scoring_rule_id')
                    ->label('Scoring Rule')
                    ->options(ScoringRule::pluck('name', 'id'))
                    ->required()
                    ->searchable(),

                Select::make('playlist_id')
                    ->label('Playlist')
                    ->options(Playlist::pluck('name', 'id'))
                    ->searchable()
                    ->placeholder('Optional'),

                TextInput::make('max_players')
                    ->numeric()
                    ->required()
                    ->default(10)
                    ->minValue(1)
                    ->maxValue(50),
                TextInput::make('id')->required()->disabled(),
                DateTimePicker::make('created_at')->disabled(),
                DateTimePicker::make('updated_at')->disabled(),
                DateTimePicker::make('started_at'),
                DateTimePicker::make('ended_at'),

            ]);
    }
}

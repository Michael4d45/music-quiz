<?php

declare(strict_types=1);

namespace App\Filament\Resources\GameSessions\Schemas;

use App\Enums\SessionStatus;
use App\Filament\Resources\Playlists\PlaylistResource;
use App\Filament\Resources\QuizModes\QuizModeResource;
use App\Filament\Resources\ScoringRules\ScoringRuleResource;
use App\Filament\Resources\Users\UserResource;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Schema;

class GameSessionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('host.name')
                ->label('Host')
                ->url(static fn($record) => (
                    $record && $record->host_id
                        ? UserResource::getUrl('edit', [
                            'record' => $record->host_id,
                        ]) : null
                ))
                ->placeholder('N/A'),

            Select::make('host_id')
                ->relationship('host', 'name')
                ->label('Host')
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

            TextEntry::make('quizMode.name')
                ->label('Quiz Mode')
                ->url(static fn($record) => (
                    $record && $record->quiz_mode_id
                        ? QuizModeResource::getUrl('edit', [
                            'record' => $record->quiz_mode_id,
                        ]) : null
                ))
                ->placeholder('N/A'),

            Select::make('quiz_mode_id')
                ->relationship('quizMode', 'name')
                ->label('Quiz Mode')
                ->required()
                ->searchable(),

            TextEntry::make('scoringRule.name')
                ->label('Scoring Rule')
                ->url(static fn($record) => (
                    $record && $record->scoring_rule_id
                        ? ScoringRuleResource::getUrl('edit', [
                            'record' => $record->scoring_rule_id,
                        ]) : null
                ))
                ->placeholder('N/A'),

            Select::make('scoring_rule_id')
                ->relationship('scoringRule', 'name')
                ->label('Scoring Rule')
                ->required()
                ->searchable(),

            TextEntry::make('playlist.name')
                ->label('Playlist')
                ->url(static fn($record) => (
                    $record && $record->playlist_id
                        ? PlaylistResource::getUrl('edit', [
                            'record' => $record->playlist_id,
                        ]) : null
                ))
                ->placeholder('N/A'),

            Select::make('playlist_id')
                ->relationship('playlist', 'name')
                ->label('Playlist')
                ->searchable()
                ->placeholder('Optional'),

            TextInput::make('max_players')
                ->numeric()
                ->required()
                ->default(10)
                ->minValue(1)
                ->maxValue(50),

            DateTimePicker::make('started_at'),
            DateTimePicker::make('ended_at'),

            Flex::make([
                TextInput::make('id')->copyable()->disabled(),
                DateTimePicker::make('created_at')->disabled(),
                DateTimePicker::make('updated_at')->disabled(),
            ])->columnSpanFull()->hiddenOn('create'),
        ]);
    }
}

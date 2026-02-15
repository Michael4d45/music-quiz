<?php

declare(strict_types=1);

namespace App\Filament\Resources\SessionFinalScores\Schemas;

use App\Filament\Resources\GameSessions\GameSessionResource;
use App\Filament\Resources\SessionParticipants\SessionParticipantResource;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Schema;

class SessionFinalScoreForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('session.room_code')
                ->label('Session')
                ->url(static fn($record) => (
                    $record && $record->session_id
                        ? GameSessionResource::getUrl('edit', [
                            'record' => $record->session_id,
                        ]) : null
                ))
                ->placeholder('N/A'),

            Select::make('session_id')
                ->relationship('session', 'room_code')
                ->required()
                ->searchable()
                ->preload(),

            TextEntry::make('participant.guest_name')
                ->label('Participant')
                ->url(static fn($record) => (
                    $record && $record->participant_id
                        ? SessionParticipantResource::getUrl('edit', [
                            'record' => $record->participant_id,
                        ]) : null
                ))
                ->placeholder('N/A'),

            Select::make('participant_id')
                ->relationship('participant', 'guest_name')
                ->required()
                ->searchable()
                ->preload(),

            TextInput::make('final_score')
                ->label('Final Score')
                ->numeric()
                ->required()
                ->minValue(0),

            TextInput::make('final_rank')
                ->label('Final Rank')
                ->numeric()
                ->required()
                ->minValue(1),

            TextInput::make('questions_answered')
                ->label('Questions Answered')
                ->numeric()
                ->required()
                ->minValue(0),

            TextInput::make('correct_answers')
                ->label('Correct Answers')
                ->numeric()
                ->required()
                ->minValue(0),

            TextInput::make('average_response_time_ms')
                ->label('Average Response Time (ms)')
                ->numeric()
                ->minValue(0)
                ->suffix('ms'),

            TextInput::make('longest_streak')
                ->label('Longest Streak')
                ->numeric()
                ->required()
                ->minValue(0),

            Flex::make([
                TextInput::make('id')->copyable()->disabled(),
                DateTimePicker::make('created_at')->disabled(),
                DateTimePicker::make('updated_at')->disabled(),
            ])->columnSpanFull()->hiddenOn('create'),
        ]);
    }
}

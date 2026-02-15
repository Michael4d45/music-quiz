<?php

declare(strict_types=1);

namespace App\Filament\Resources\SessionRounds\Schemas;

use App\Filament\Resources\GameSessions\GameSessionResource;
use App\Filament\Resources\QuizQuestions\QuizQuestionResource;
use App\Filament\Resources\SessionParticipants\SessionParticipantResource;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SessionRoundForm
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

            TextInput::make('round_number')
                ->label('Round Number')
                ->numeric()
                ->required()
                ->minValue(1),

            TextEntry::make('question.prompt_text')
                ->label('Question')
                ->url(static fn($record) => (
                    $record && $record->question_id
                        ? QuizQuestionResource::getUrl('edit', [
                            'record' => $record->question_id,
                        ]) : null
                ))
                ->placeholder('N/A'),

            Select::make('question_id')
                ->label('Question')
                ->relationship('question', 'prompt_text')
                ->required()
                ->searchable()
                ->preload(),

            DateTimePicker::make('started_at')->label('Started At'),

            DateTimePicker::make('ended_at')->label('Ended At'),

            TextEntry::make('firstBuzzer.guest_name')
                ->label('First Buzzer')
                ->url(static fn($record) => (
                    $record && $record->first_buzzer_id
                        ? SessionParticipantResource::getUrl('edit', [
                            'record' => $record->first_buzzer_id,
                        ]) : null
                ))
                ->placeholder('N/A'),

            Select::make('first_buzzer_id')
                ->relationship('firstBuzzer', 'guest_name')
                ->searchable()
                ->preload(),
        ]);
    }
}

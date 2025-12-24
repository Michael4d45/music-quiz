<?php

declare(strict_types=1);

namespace App\Filament\Resources\SessionFinalScores\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SessionFinalScoreForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('session_id')
                    ->label('Session')
                    ->relationship('session', 'id')
                    ->required()
                    ->searchable()
                    ->preload(),

                Select::make('participant_id')
                    ->label('Participant')
                    ->relationship('participant', 'id')
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
                TextInput::make('id')->required()->disabled(),
                DateTimePicker::make('created_at')->disabled(),
                DateTimePicker::make('updated_at')->disabled(),

            ]);
    }
}

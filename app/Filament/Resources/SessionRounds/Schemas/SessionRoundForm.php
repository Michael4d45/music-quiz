<?php

declare(strict_types=1);

namespace App\Filament\Resources\SessionRounds\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SessionRoundForm
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

                TextInput::make('round_number')
                    ->label('Round Number')
                    ->numeric()
                    ->required()
                    ->minValue(1),

                Select::make('question_id')
                    ->label('Question')
                    ->relationship('question', 'question_text')
                    ->required()
                    ->searchable()
                    ->preload(),

                DateTimePicker::make('started_at')
                    ->label('Started At'),

                DateTimePicker::make('ended_at')
                    ->label('Ended At'),

                Select::make('first_buzzer_id')
                    ->label('First Buzzer')
                    ->relationship('firstBuzzer', 'id')
                    ->searchable()
                    ->preload(),
                TextInput::make('id')->required()->disabled(),

            ]);
    }
}

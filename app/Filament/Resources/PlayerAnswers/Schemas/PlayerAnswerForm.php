<?php

declare(strict_types=1);

namespace App\Filament\Resources\PlayerAnswers\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PlayerAnswerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('round_id')
                    ->label('Round')
                    ->relationship('round', 'id')
                    ->required()
                    ->searchable()
                    ->preload(),

                Select::make('participant_id')
                    ->label('Participant')
                    ->relationship('participant', 'id')
                    ->required()
                    ->searchable()
                    ->preload(),

                Textarea::make('submitted_text')
                    ->label('Submitted Text')
                    ->rows(3)
                    ->columnSpanFull(),

                Select::make('selected_option_id')
                    ->label('Selected Option')
                    ->relationship('selectedOption', 'option_text')
                    ->searchable()
                    ->preload(),

                Select::make('matched_variant_id')
                    ->label('Matched Variant')
                    ->relationship('matchedVariant', 'accepted_text')
                    ->searchable()
                    ->preload(),

                Toggle::make('is_correct')
                    ->label('Is Correct')
                    ->default(false),

                TextInput::make('response_time_ms')
                    ->label('Response Time (ms)')
                    ->numeric()
                    ->minValue(0)
                    ->suffix('ms'),

                TextInput::make('points_awarded')
                    ->label('Points Awarded')
                    ->numeric()
                    ->default(0),

                Toggle::make('host_override')
                    ->label('Host Override')
                    ->helperText('Whether this answer was manually corrected by the host'),
                TextInput::make('id')->required()->disabled(),
                DateTimePicker::make('created_at')->disabled(),
                DateTimePicker::make('updated_at')->disabled(),

            ]);
    }
}

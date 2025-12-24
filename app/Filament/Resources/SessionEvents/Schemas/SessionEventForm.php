<?php

declare(strict_types=1);

namespace App\Filament\Resources\SessionEvents\Schemas;

use App\Enums\EventType;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SessionEventForm
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

                Select::make('event_type')
                    ->label('Event Type')
                    ->options(EventType::class)
                    ->required()
                    ->enum(EventType::class),

                Select::make('participant_id')
                    ->label('Participant')
                    ->relationship('participant', 'id')
                    ->searchable()
                    ->preload(),

                KeyValue::make('payload')
                    ->label('Payload')
                    ->keyLabel('Key')
                    ->valueLabel('Value')
                    ->columnSpanFull()
                    ->helperText('Additional event data as key-value pairs'),
                TextInput::make('id')->required()->disabled(),
                DateTimePicker::make('created_at')->disabled(),
                DateTimePicker::make('updated_at')->disabled(),

            ]);
    }
}

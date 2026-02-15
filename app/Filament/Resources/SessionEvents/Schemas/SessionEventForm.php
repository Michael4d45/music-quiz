<?php

declare(strict_types=1);

namespace App\Filament\Resources\SessionEvents\Schemas;

use App\Enums\EventType;
use App\Filament\Resources\GameSessions\GameSessionResource;
use App\Filament\Resources\SessionParticipants\SessionParticipantResource;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Schema;

class SessionEventForm
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

            Select::make('event_type')
                ->label('Event Type')
                ->options(EventType::class)
                ->required()
                ->enum(EventType::class),

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
                ->searchable()
                ->preload(),

            KeyValue::make('payload')
                ->label('Payload')
                ->keyLabel('Key')
                ->valueLabel('Value')
                ->columnSpanFull()
                ->helperText('Additional event data as key-value pairs'),

            Flex::make([
                TextInput::make('id')->copyable()->disabled(),
                DateTimePicker::make('created_at')->disabled(),
                DateTimePicker::make('updated_at')->disabled(),
            ])->columnSpanFull()->hiddenOn('create'),
        ]);
    }
}

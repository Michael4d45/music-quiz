<?php

declare(strict_types=1);

namespace App\Filament\Resources\SessionParticipants\Schemas;

use App\Enums\Role;
use App\Filament\Resources\GameSessions\GameSessionResource;
use App\Filament\Resources\Users\UserResource;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SessionParticipantForm
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

            TextEntry::make('user.name')
                ->label('User')
                ->url(static fn($record) => (
                    $record && $record->user_id
                        ? UserResource::getUrl('edit', [
                            'record' => $record->user_id,
                        ]) : null
                ))
                ->placeholder('N/A'),

            Select::make('user_id')
                ->label('User')
                ->relationship('user', 'name')
                ->searchable()
                ->preload(),

            TextInput::make('guest_name')->label('Guest Name')->maxLength(255),

            Select::make('role')
                ->label('Role')
                ->options(Role::class)
                ->required()
                ->enum(Role::class)
                ->default(Role::Player),

            TextInput::make('current_total_score')
                ->label('Current Total Score')
                ->numeric()
                ->required()
                ->default(0)
                ->minValue(0),

            Toggle::make('is_connected')->label('Is Connected')->default(true),

            DateTimePicker::make('joined_at')
                ->label('Joined At')
                ->required()
                ->default(now()),

            DateTimePicker::make('buzzed_in_at')->label('Buzzed In At'),

            TextInput::make('id')->copyable()->disabled(),
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Filament\Resources\GameSessions\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EventsRelationManager extends RelationManager
{
    protected static string $relationship = 'events';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->with([
                'session',
                'participant',
                'participant.user',
            ]))
            ->columns([
                TextColumn::make('id')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('session.room_code')
                    ->label('Session')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('event_type')
                    ->label('Event Type')
                    ->badge()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('participant.user.name')
                    ->label('Participant User')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Guest')
                    ->toggleable(),

                TextColumn::make('participant.guest_name')
                    ->label('Participant Guest')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Registered User')
                    ->toggleable(),

                TextColumn::make('participant.role')
                    ->label('Participant Role')
                    ->badge()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('payload')
                    ->label('Payload')
                    ->formatStateUsing(
                        fn($state) => json_encode($state, JSON_PRETTY_PRINT),
                    )
                    ->wrap()
                    ->limit(100)
                    ->tooltip(
                        fn($state) => json_encode($state, JSON_PRETTY_PRINT),
                    )
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Event Time')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->defaultSort('created_at', 'desc');
    }
}

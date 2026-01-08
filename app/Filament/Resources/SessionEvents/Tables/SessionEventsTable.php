<?php

declare(strict_types=1);

namespace App\Filament\Resources\SessionEvents\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SessionEventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->with([
                'session',
                'participant',
            ]))
            ->columns([
                TextColumn::make('id')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('session.room_code')
                    ->label('Session')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('event_type')
                    ->badge()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('participant.user.name')
                    ->label('Participant')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('payload')
                    ->label('Payload')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): string|null {
                        $state = $column->getState();
                        if (is_array($state)) {
                            $encoded = json_encode($state, JSON_PRETTY_PRINT);

                            return $encoded !== false
                                ? $encoded
                                : 'Invalid JSON';
                        }

                        return null;
                    })
                    ->formatStateUsing(fn($state) => (
                        is_array($state)
                            ? (json_encode($state) ?: 'Invalid JSON')
                            : $state
                    ))
                    ->toggleable(),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}

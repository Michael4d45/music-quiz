<?php

declare(strict_types=1);

namespace App\Filament\Resources\SessionEvents\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SessionEventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('session.id')
                    ->label('Session')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('event_type')
                    ->label('Event Type')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('participant.id')
                    ->label('Participant')
                    ->searchable(),

                TextColumn::make('payload')
                    ->label('Payload')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): string|null {
                        $state = $column->getState();
                        if (is_array($state)) {
                            $encoded = json_encode($state, JSON_PRETTY_PRINT);

                            return $encoded !== false ? $encoded : 'Invalid JSON';
                        }

                        return null;
                    })
                    ->formatStateUsing(fn ($state) => is_array($state) ? (json_encode($state) ?: 'Invalid JSON') : $state),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}

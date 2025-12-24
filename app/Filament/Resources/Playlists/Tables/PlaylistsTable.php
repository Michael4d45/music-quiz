<?php

declare(strict_types=1);

namespace App\Filament\Resources\Playlists\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PlaylistsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Owner')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('description')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): string|null {
                        $state = $column->getState();
                        if (is_string($state) && strlen($state) > 50) {
                            return $state;
                        }

                        return null;
                    }),

                IconColumn::make('is_public')
                    ->label('Public')
                    ->boolean(),

                TextColumn::make('play_count')
                    ->label('Plays')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('items_count')
                    ->label('Items')
                    ->getStateUsing(fn ($record) => $record->items()->count())
                    ->numeric(),

                TextColumn::make('game_sessions_count')
                    ->label('Sessions')
                    ->getStateUsing(fn ($record) => $record->gameSessions()->count())
                    ->numeric(),

                TextColumn::make('created_at')
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

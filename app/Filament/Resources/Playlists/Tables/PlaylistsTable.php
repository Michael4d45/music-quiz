<?php

declare(strict_types=1);

namespace App\Filament\Resources\Playlists\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PlaylistsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query
                ->with('user')
                ->withCount(['items', 'gameSessions']))
            ->columns([
                TextColumn::make('id')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('user.name')
                    ->label('Owner')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('description')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): string|null {
                        $state = $column->getState();
                        if (is_string($state) && strlen($state) > 50) {
                            return $state;
                        }
                        return null;
                    })
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('visibility')
                    ->badge()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('estimated_duration_minutes')
                    ->label('Duration (min)')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('question_order')
                    ->badge()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('items_count')
                    ->label('Items')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('game_sessions_count')
                    ->label('Game Sessions')
                    ->numeric()
                    ->sortable()
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
                SelectFilter::make('user_id')
                    ->label('Creator')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('visibility')
                    ->label('Visibility')
                    ->options(\App\Enums\Visibility::class),
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

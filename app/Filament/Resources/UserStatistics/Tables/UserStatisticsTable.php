<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserStatistics\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UserStatisticsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->with([
                'user',
                'favoriteCategory',
            ]))
            ->columns([
                TextColumn::make('id')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('total_games_played')
                    ->label('Games Played')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('total_wins')
                    ->label('Wins')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('total_points')
                    ->label('Total Points')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('average_score')
                    ->label('Average Score')
                    ->numeric(2)
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('best_streak')
                    ->label('Best Streak')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('favoriteCategory.name')
                    ->label('Favorite Category')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('updated_at')
                    ->label('Last Updated')
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
            ->defaultSort('total_points', 'desc');
    }
}

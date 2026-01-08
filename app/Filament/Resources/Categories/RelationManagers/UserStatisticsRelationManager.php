<?php

declare(strict_types=1);

namespace App\Filament\Resources\Categories\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UserStatisticsRelationManager extends RelationManager
{
    protected static string $relationship = 'userStatistics';

    public function table(Table $table): Table
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
                    ->label('Total Wins')
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
                    ->numeric(decimalPlaces: 2)
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
                    ->label('Updated At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->defaultSort('total_games_played', 'desc');
    }
}

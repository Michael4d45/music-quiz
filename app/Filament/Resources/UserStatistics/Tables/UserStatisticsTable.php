<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserStatistics\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UserStatisticsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('total_games_played')
                    ->label('Games Played')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('total_wins')
                    ->label('Wins')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('total_points')
                    ->label('Total Points')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('average_score')
                    ->label('Average Score')
                    ->numeric()
                    ->formatStateUsing(fn ($state) => $state ? number_format((float) $state, 2) : '0.00')
                    ->sortable(),

                TextColumn::make('best_streak')
                    ->label('Best Streak')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('favoriteCategory.name')
                    ->label('Favorite Category')
                    ->searchable(),

                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime()
                    ->sortable(),
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
            ->defaultSort('total_points', 'desc');
    }
}

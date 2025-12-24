<?php

declare(strict_types=1);

namespace App\Filament\Resources\ScoringRules\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ScoringRulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('base_points')
                    ->label('Base Points')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('decay_factor')
                    ->label('Decay Factor')
                    ->numeric(2)
                    ->sortable(),

                TextColumn::make('max_time_ms')
                    ->label('Max Time (ms)')
                    ->numeric()
                    ->sortable(),

                IconColumn::make('streak_bonus_enabled')
                    ->label('Streak Bonus')
                    ->boolean(),

                TextColumn::make('streak_multiplier')
                    ->label('Streak Multiplier')
                    ->numeric(2)
                    ->sortable(),

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

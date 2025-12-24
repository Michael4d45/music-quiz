<?php

declare(strict_types=1);

namespace App\Filament\Resources\GameSessions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GameSessionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('room_code')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('host.name')
                    ->label('Host')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'waiting' => 'gray',
                        'active' => 'success',
                        'completed' => 'info',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('quizMode.name')
                    ->label('Quiz Mode')
                    ->searchable(),

                TextColumn::make('scoringRule.name')
                    ->label('Scoring Rule')
                    ->searchable(),

                TextColumn::make('max_players')
                    ->label('Max Players')
                    ->numeric(),

                TextColumn::make('participants_count')
                    ->label('Current Players')
                    ->getStateUsing(fn ($record) => $record->participants()->count())
                    ->numeric(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('started_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('ended_at')
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

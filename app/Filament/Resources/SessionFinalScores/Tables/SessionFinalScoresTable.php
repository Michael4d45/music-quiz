<?php

declare(strict_types=1);

namespace App\Filament\Resources\SessionFinalScores\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SessionFinalScoresTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('session.id')
                    ->label('Session')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('participant.id')
                    ->label('Participant')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('final_score')
                    ->label('Final Score')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('final_rank')
                    ->label('Rank')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('questions_answered')
                    ->label('Questions Answered')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('correct_answers')
                    ->label('Correct Answers')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('average_response_time_ms')
                    ->label('Avg Response Time')
                    ->suffix(' ms')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('longest_streak')
                    ->label('Longest Streak')
                    ->numeric()
                    ->sortable(),

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
            ->defaultSort('final_rank');
    }
}

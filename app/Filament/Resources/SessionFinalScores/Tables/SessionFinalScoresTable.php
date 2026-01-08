<?php

declare(strict_types=1);

namespace App\Filament\Resources\SessionFinalScores\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SessionFinalScoresTable
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

                TextColumn::make('participant.user.name')
                    ->label('Participant')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('final_score')
                    ->label('Final Score')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('final_rank')
                    ->label('Final Rank')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('questions_answered')
                    ->label('Questions Answered')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('correct_answers')
                    ->label('Correct Answers')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('average_response_time_ms')
                    ->label('Avg Response Time (ms)')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('longest_streak')
                    ->label('Longest Streak')
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
                //
            ])
            ->recordActions([])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('final_rank');
    }
}

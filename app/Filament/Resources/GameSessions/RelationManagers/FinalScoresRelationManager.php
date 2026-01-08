<?php

declare(strict_types=1);

namespace App\Filament\Resources\GameSessions\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FinalScoresRelationManager extends RelationManager
{
    protected static string $relationship = 'finalScores';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->with([
                'session',
                'participant',
                'participant.user',
            ]))
            ->columns([
                TextColumn::make('id')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('session.room_code')
                    ->label('Session')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('participant.user.name')
                    ->label('Participant User')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Guest')
                    ->toggleable(),

                TextColumn::make('participant.guest_name')
                    ->label('Participant Guest')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Registered User')
                    ->toggleable(),

                TextColumn::make('participant.role')
                    ->label('Role')
                    ->badge()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('final_rank')
                    ->label('Final Rank')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('final_score')
                    ->label('Final Score')
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

                TextColumn::make('created_at')
                    ->label('Calculated At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->defaultSort('final_rank', 'asc');
    }
}

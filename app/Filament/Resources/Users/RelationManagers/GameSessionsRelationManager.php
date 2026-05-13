<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GameSessionsRelationManager extends RelationManager
{
    protected static string $relationship = 'gameSessions';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->with([
                'host',
                'quizMode',
                'scoringRule',
                'playlist',
            ])->withCount([
                'participants',
                'rounds',
                'events',
                'finalScores',
            ]))
            ->columns([
                TextColumn::make('id')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('room_code')
                    ->label('Room Code')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('host.name')
                    ->label('Host')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')->badge()->sortable()->toggleable(),

                TextColumn::make('quizMode.name')
                    ->label('Quiz Mode')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('scoringRule.name')
                    ->label('Scoring Rule')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('playlist.name')
                    ->label('Playlist')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('max_players')
                    ->label('Max Players')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('participants_count')
                    ->label('Participants')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('rounds_count')
                    ->label('Rounds')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('events_count')
                    ->label('Events')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('final_scores_count')
                    ->label('Final Scores')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('started_at')
                    ->label('Started At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('ended_at')
                    ->label('Ended At')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Not ended')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->defaultSort('created_at', 'desc');
    }
}

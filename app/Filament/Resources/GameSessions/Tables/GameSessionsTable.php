<?php

declare(strict_types=1);

namespace App\Filament\Resources\GameSessions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class GameSessionsTable
{
    public static function configure(Table $table): Table
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
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('host.name')
                    ->label('Host')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

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
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('ended_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

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
                SelectFilter::make('host_id')
                    ->label('Host')
                    ->relationship('host', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(\App\Enums\SessionStatus::class),
                SelectFilter::make('quiz_mode_id')
                    ->label('Quiz Mode')
                    ->relationship('quizMode', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('scoring_rule_id')
                    ->label('Scoring Rule')
                    ->relationship('scoringRule', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('playlist_id')
                    ->label('Playlist')
                    ->relationship('playlist', 'name')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('started_at')
                    ->label('Has Started')
                    ->placeholder('All')
                    ->trueLabel('Started')
                    ->falseLabel('Not Started'),
                TernaryFilter::make('ended_at')
                    ->label('Has Ended')
                    ->placeholder('All')
                    ->trueLabel('Ended')
                    ->falseLabel('Not Ended'),
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

<?php

declare(strict_types=1);

namespace App\Filament\Resources\GameSessions\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RoundsRelationManager extends RelationManager
{
    protected static string $relationship = 'rounds';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn($query) => $query->with([
                    'session',
                    'question',
                    'firstBuzzer',
                ])->withCount('answers'),
            )
            ->columns([
                TextColumn::make('id')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('session.room_code')
                    ->label('Session')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('round_number')
                    ->label('Round Number')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('question.prompt_text')
                    ->label('Question')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('firstBuzzer.user.name')
                    ->label('First Buzzer')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('answers_count')
                    ->label('Answers')
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
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->defaultSort('round_number');
    }
}

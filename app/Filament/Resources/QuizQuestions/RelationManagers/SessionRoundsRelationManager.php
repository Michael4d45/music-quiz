<?php

declare(strict_types=1);

namespace App\Filament\Resources\QuizQuestions\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SessionRoundsRelationManager extends RelationManager
{
    protected static string $relationship = 'sessionRounds';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn($query) => $query
                    ->with(
                        'session',
                        'question',
                        'question.track',
                        'firstBuzzer',
                        'firstBuzzer.user',
                    )
                    ->withCount('answers'),
            )
            ->columns([
                TextColumn::make('id')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('session.room_code')
                    ->label('Session')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('round_number')
                    ->label('Round Number')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('question.track.title')
                    ->label('Track')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('question.track.artist_name')
                    ->label('Artist')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('question.question_type')
                    ->label('Question Type')
                    ->badge()
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
                    ->placeholder('No buzzer')
                    ->toggleable(),

                TextColumn::make('firstBuzzer.guest_name')
                    ->label('First Buzzer Guest')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Registered user')
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
                    ->placeholder('Not ended')
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->defaultSort('round_number', 'desc');
    }
}

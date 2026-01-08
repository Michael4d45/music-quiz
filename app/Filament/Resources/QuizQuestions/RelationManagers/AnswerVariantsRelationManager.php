<?php

declare(strict_types=1);

namespace App\Filament\Resources\QuizQuestions\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class AnswerVariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'answerVariants';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->with('playerAnswers'))
            ->columns([
                TextColumn::make('id')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('player_answers_count')
                    ->label('Player Answers')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('accepted_text')
                    ->label('Accepted Text')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                TernaryFilter::make('player_answers_count')
                    ->label('Has Player Answers')
                    ->placeholder('All')
                    ->trueLabel('With Answers')
                    ->falseLabel('Without Answers')
                    ->queries(
                        true: fn($query) => $query->having(
                            'player_answers_count',
                            '>',
                            0,
                        ),
                        false: fn($query) => $query->having(
                            'player_answers_count',
                            '=',
                            0,
                        ),
                    ),
            ])
            ->defaultSort('accepted_text', 'asc');
    }
}

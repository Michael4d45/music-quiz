<?php

declare(strict_types=1);

namespace App\Filament\Resources\AnswerVariants\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class AnswerVariantsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn($query) => $query
                    ->with('question')
                    ->withCount('playerAnswers'),
            )
            ->columns([
                TextColumn::make('id')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('question.prompt_text')
                    ->label('Question')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('accepted_text')
                    ->label('Accepted Text')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('player_answers_count')
                    ->label('Player Answers')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('question_id')
                    ->label('Question')
                    ->relationship('question', 'prompt_text')
                    ->searchable()
                    ->preload(),
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
            ->recordActions([])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

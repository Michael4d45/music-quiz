<?php

declare(strict_types=1);

namespace App\Filament\Resources\MultipleChoiceOptions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class MultipleChoiceOptionsTable
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
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('option_text')
                    ->label('Option Text')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                IconColumn::make('is_correct')
                    ->label('Correct')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('sort_order')
                    ->label('Sort Order')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('player_answers_count')
                    ->label('Player Answers')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('question_id')
                    ->label('Question')
                    ->relationship('question', 'prompt_text')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_correct')
                    ->label('Correct Answer')
                    ->placeholder('All')
                    ->trueLabel('Correct')
                    ->falseLabel('Incorrect'),
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
            ])
            ->defaultSort('sort_order');
    }
}

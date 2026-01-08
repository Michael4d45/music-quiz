<?php

declare(strict_types=1);

namespace App\Filament\Resources\QuizQuestions\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class MultipleChoiceOptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'multipleChoiceOptions';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn($query) => $query->with('question', 'question.track'),
            )
            ->columns([
                TextColumn::make('id')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

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

                TextColumn::make('option_text')
                    ->label('Option Text')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                IconColumn::make('is_correct')
                    ->label('Is Correct')
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
            ])
            ->filters([
                TernaryFilter::make('is_correct')
                    ->label('Correct Answer')
                    ->placeholder('All')
                    ->trueLabel('Correct')
                    ->falseLabel('Incorrect'),
            ])
            ->defaultSort('sort_order', 'asc');
    }
}

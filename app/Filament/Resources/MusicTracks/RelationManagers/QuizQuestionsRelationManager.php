<?php

declare(strict_types=1);

namespace App\Filament\Resources\MusicTracks\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class QuizQuestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'quizQuestions';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('prompt_text')
            ->columns([
                TextColumn::make('question_type')
                    ->badge()
                    ->searchable(),

                TextColumn::make('prompt_text')
                    ->label('Question')
                    ->limit(50)
                    ->searchable(),

                TextColumn::make('correct_answer')
                    ->label('Correct Answer')
                    ->limit(30)
                    ->searchable(),

                TextColumn::make('base_points')
                    ->label('Points')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('difficulty_level')
                    ->label('Difficulty')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('media_start_seconds')
                    ->label('Start Time')
                    ->formatStateUsing(fn ($state) => $state ? gmdate('i:s', $state) : '-')
                    ->sortable(),

                TextColumn::make('media_end_seconds')
                    ->label('End Time')
                    ->formatStateUsing(fn ($state) => $state ? gmdate('i:s', $state) : '-')
                    ->sortable(),

                TextColumn::make('answer_variants_count')
                    ->label('Variants')
                    ->getStateUsing(fn ($record) => $record->answerVariants()->count())
                    ->numeric(),

                TextColumn::make('multiple_choice_options_count')
                    ->label('Options')
                    ->getStateUsing(fn ($record) => $record->multipleChoiceOptions()->count())
                    ->numeric(),

                TextColumn::make('session_rounds_count')
                    ->label('Used In Rounds')
                    ->getStateUsing(fn ($record) => $record->sessionRounds()->count())
                    ->numeric(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteAction::make(),
                ]),
            ])
            ->defaultSort('difficulty_level');
    }
}

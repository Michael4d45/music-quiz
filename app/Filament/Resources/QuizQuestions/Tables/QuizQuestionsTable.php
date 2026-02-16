<?php

declare(strict_types=1);

namespace App\Filament\Resources\QuizQuestions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class QuizQuestionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query
                ->with('track')
                ->withCount([
                    'answerVariants',
                    'multipleChoiceOptions',
                    'sessionRounds',
                    'playlistItems',
                ]))
            ->columns([
                TextColumn::make('id')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('track.title')
                    ->label('Track')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('track.artist_name')
                    ->label('Artist')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('question_type')
                    ->badge()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('prompt_text')
                    ->label('Prompt')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('correct_answer')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('base_points')
                    ->label('Base Points')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('media_start_seconds')
                    ->label('Media Start (s)')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('media_end_seconds')
                    ->label('Media End (s)')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('difficulty_level')
                    ->label('Difficulty Level')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('visibility')
                    ->badge()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('last_tested_at')
                    ->label('Last Tested')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('answer_variants_count')
                    ->label('Answer Variants')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('multiple_choice_options_count')
                    ->label('Multiple Choice Options')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('session_rounds_count')
                    ->label('Session Rounds')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('playlist_items_count')
                    ->label('Playlist Items')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

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
                SelectFilter::make('track_id')
                    ->label('Track')
                    ->relationship('track', 'title')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('question_type')
                    ->label('Question Type')
                    ->options(\App\Enums\QuestionType::class),
                SelectFilter::make('visibility')
                    ->label('Visibility')
                    ->options(\App\Enums\Visibility::class),
                SelectFilter::make('difficulty_level')
                    ->label('Difficulty Level')
                    ->options([
                        1 => 'Easy',
                        2 => 'Medium',
                        3 => 'Hard',
                    ]),
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

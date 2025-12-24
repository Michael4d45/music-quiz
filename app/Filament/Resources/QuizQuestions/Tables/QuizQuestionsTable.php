<?php

declare(strict_types=1);

namespace App\Filament\Resources\QuizQuestions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class QuizQuestionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('track.title')
                    ->label('Track')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                TextColumn::make('track.artist')
                    ->label('Artist')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                TextColumn::make('question_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'artist' => 'success',
                        'title' => 'info',
                        'year' => 'warning',
                        'multiple_choice' => 'primary',
                        'lyric' => 'secondary',
                        'audio_clip' => 'gray',
                        default => 'gray',
                    }),

                TextColumn::make('correct_answer')
                    ->searchable()
                    ->limit(30),

                TextColumn::make('base_points')
                    ->label('Points')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('difficulty_level')
                    ->label('Difficulty')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}

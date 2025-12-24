<?php

declare(strict_types=1);

namespace App\Filament\Resources\AnswerVariants\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AnswerVariantsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('question.question_text')
                    ->label('Question')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): string|null {
                        $state = $column->getState();
                        if (is_string($state) && strlen($state) > 50) {
                            return $state;
                        }

                        return null;
                    })
                    ->searchable()
                    ->sortable(),

                TextColumn::make('accepted_text')
                    ->label('Accepted Text')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): string|null {
                        $state = $column->getState();
                        if (is_string($state) && strlen($state) > 50) {
                            return $state;
                        }

                        return null;
                    })
                    ->searchable(),

                TextColumn::make('player_answers_count')
                    ->label('Player Answers')
                    ->getStateUsing(fn ($record) => $record->playerAnswers()->count())
                    ->numeric(),
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
            ->defaultSort('question.question_text');
    }
}

<?php

declare(strict_types=1);

namespace App\Filament\Resources\MultipleChoiceOptions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MultipleChoiceOptionsTable
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

                TextColumn::make('option_text')
                    ->label('Option Text')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): string|null {
                        $state = $column->getState();
                        if (is_string($state) && strlen($state) > 50) {
                            return $state;
                        }

                        return null;
                    })
                    ->searchable(),

                IconColumn::make('is_correct')
                    ->label('Correct')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),

                TextColumn::make('sort_order')
                    ->label('Sort Order')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('player_answers_count')
                    ->label('Times Selected')
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

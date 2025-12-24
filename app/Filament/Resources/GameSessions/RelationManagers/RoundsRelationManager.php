<?php

declare(strict_types=1);

namespace App\Filament\Resources\GameSessions\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RoundsRelationManager extends RelationManager
{
    protected static string $relationship = 'rounds';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('round_number')
            ->columns([
                TextColumn::make('round_number')
                    ->label('Round')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('question.question_text')
                    ->label('Question')
                    ->limit(50)
                    ->searchable(),

                TextColumn::make('firstBuzzer.user.name')
                    ->label('First Buzzer')
                    ->placeholder('No one'),

                TextColumn::make('started_at')
                    ->label('Started At')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('ended_at')
                    ->label('Ended At')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('answers_count')
                    ->label('Answers')
                    ->getStateUsing(fn ($record) => $record->answers()->count())
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
            ->defaultSort('round_number');
    }
}

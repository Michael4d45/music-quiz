<?php

declare(strict_types=1);

namespace App\Filament\Resources\SessionRounds\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SessionRoundsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('session.id')
                    ->label('Session')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('round_number')
                    ->label('Round')
                    ->numeric()
                    ->sortable(),

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
                    ->searchable(),

                TextColumn::make('started_at')
                    ->label('Started At')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('ended_at')
                    ->label('Ended At')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('firstBuzzer.id')
                    ->label('First Buzzer')
                    ->searchable(),

                TextColumn::make('answers_count')
                    ->label('Answers')
                    ->getStateUsing(fn ($record) => $record->answers()->count())
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
            ->defaultSort('round_number');
    }
}

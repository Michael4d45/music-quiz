<?php

declare(strict_types=1);

namespace App\Filament\Resources\PlayerAnswers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PlayerAnswersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('round.id')
                    ->label('Round')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('participant.id')
                    ->label('Participant')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('submitted_text')
                    ->label('Submitted Text')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): string|null {
                        $state = $column->getState();
                        if (is_string($state) && strlen($state) > 50) {
                            return $state;
                        }

                        return null;
                    })
                    ->searchable(),

                TextColumn::make('selectedOption.option_text')
                    ->label('Selected Option')
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): string|null {
                        $state = $column->getState();
                        if (is_string($state) && strlen($state) > 30) {
                            return $state;
                        }

                        return null;
                    }),

                IconColumn::make('is_correct')
                    ->label('Correct')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('response_time_ms')
                    ->label('Response Time')
                    ->suffix(' ms')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('points_awarded')
                    ->label('Points')
                    ->numeric()
                    ->sortable(),

                IconColumn::make('host_override')
                    ->label('Host Override')
                    ->boolean(),

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

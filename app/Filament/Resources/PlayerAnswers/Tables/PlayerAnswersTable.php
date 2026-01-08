<?php

declare(strict_types=1);

namespace App\Filament\Resources\PlayerAnswers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PlayerAnswersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->with([
                'round.session',
                'participant',
                'selectedOption',
                'matchedVariant',
            ]))
            ->columns([
                TextColumn::make('id')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('round_id')
                    ->label('Round ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('round.round_number')
                    ->label('Round Number')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('round.session.room_code')
                    ->label('Session')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('participant.user.name')
                    ->label('Participant')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('submitted_text')
                    ->label('Submitted Text')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('selectedOption.option_text')
                    ->label('Selected Option')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('matchedVariant.accepted_text')
                    ->label('Matched Variant')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                IconColumn::make('is_correct')
                    ->label('Correct')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('response_time_ms')
                    ->label('Response Time (ms)')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('points_awarded')
                    ->label('Points Awarded')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                IconColumn::make('host_override')
                    ->label('Host Override')
                    ->boolean()
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
                SelectFilter::make('round_id')
                    ->label('Round')
                    ->relationship('round.session.room_code', 'room_code')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('participant_id')
                    ->label('Participant')
                    ->relationship('participant.user.name', 'name')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_correct')
                    ->label('Correctness')
                    ->placeholder('All')
                    ->trueLabel('Correct')
                    ->falseLabel('Incorrect'),
                TernaryFilter::make('host_override')
                    ->label('Host Override')
                    ->placeholder('All')
                    ->trueLabel('Overridden')
                    ->falseLabel('Not Overridden'),
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

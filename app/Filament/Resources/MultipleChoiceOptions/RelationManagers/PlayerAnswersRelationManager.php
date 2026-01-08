<?php

declare(strict_types=1);

namespace App\Filament\Resources\MultipleChoiceOptions\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PlayerAnswersRelationManager extends RelationManager
{
    protected static string $relationship = 'playerAnswers';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->with([
                'round',
                'round.session',
                'participant',
                'participant.user',
                'matchedVariant',
            ]))
            ->columns([
                TextColumn::make('id')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('round.session.room_code')
                    ->label('Session')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('round.round_number')
                    ->label('Round')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('participant.user.name')
                    ->label('Player')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Guest')
                    ->toggleable(),

                TextColumn::make('participant.guest_name')
                    ->label('Guest Name')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Registered User')
                    ->toggleable(),

                TextColumn::make('submitted_text')
                    ->label('Submitted Text')
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
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Submitted At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->defaultSort('created_at', 'desc');
    }
}

<?php

declare(strict_types=1);

namespace App\Filament\Resources\SessionParticipants\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SessionParticipantsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('session.id')
                    ->label('Session')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('guest_name')
                    ->label('Guest Name')
                    ->searchable(),

                TextColumn::make('role')
                    ->label('Role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'host' => 'danger',
                        'player' => 'success',
                        'spectator' => 'gray',
                        default => 'gray',
                    }),

                TextColumn::make('current_total_score')
                    ->label('Current Score')
                    ->numeric()
                    ->sortable(),

                IconColumn::make('is_connected')
                    ->label('Connected')
                    ->boolean()
                    ->trueIcon('heroicon-o-wifi')
                    ->falseIcon('heroicon-o-wifi-slash')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('joined_at')
                    ->label('Joined At')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('buzzed_in_at')
                    ->label('Buzzed In At')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Not buzzed in'),

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
            ->defaultSort('joined_at', 'desc');
    }
}

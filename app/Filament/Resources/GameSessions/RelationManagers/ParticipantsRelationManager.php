<?php

declare(strict_types=1);

namespace App\Filament\Resources\GameSessions\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ParticipantsRelationManager extends RelationManager
{
    protected static string $relationship = 'participants';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->with([
                'session',
                'user',
            ])->withCount(['answers', 'finalScore']))
            ->columns([
                TextColumn::make('id')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('session.room_code')
                    ->label('Session')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('guest_name')
                    ->label('Guest Name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('role')
                    ->label('Role')
                    ->badge()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('current_total_score')
                    ->label('Current Total Score')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                IconColumn::make('is_connected')
                    ->label('Connected')
                    ->boolean()
                    ->trueIcon('heroicon-o-wifi')
                    ->falseIcon('heroicon-o-wifi-slash')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('joined_at')
                    ->label('Joined At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('buzzed_in_at')
                    ->label('Buzzed In At')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Not buzzed in')
                    ->toggleable(),

                TextColumn::make('answers_count')
                    ->label('Answers')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('final_score_count')
                    ->label('Final Score')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->defaultSort('joined_at', 'desc');
    }
}

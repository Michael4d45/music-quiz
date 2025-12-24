<?php

declare(strict_types=1);

namespace App\Filament\Resources\GameSessions\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
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
            ->recordTitleAttribute('guest_name')
            ->columns([
                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->placeholder('Guest'),

                TextColumn::make('guest_name')
                    ->label('Guest Name')
                    ->searchable()
                    ->placeholder('Registered User'),

                TextColumn::make('role')
                    ->badge(),

                TextColumn::make('current_total_score')
                    ->label('Current Score')
                    ->numeric()
                    ->sortable(),

                IconColumn::make('is_connected')
                    ->label('Connected')
                    ->boolean(),

                TextColumn::make('joined_at')
                    ->label('Joined At')
                    ->dateTime()
                    ->sortable(),
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
            ->defaultSort('joined_at', 'desc');
    }
}

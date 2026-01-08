<?php

declare(strict_types=1);

namespace App\Filament\Resources\Playlists\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn($query) => $query->with(
                    'playlist',
                    'question',
                    'question.track',
                ),
            )
            ->columns([
                TextColumn::make('id')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('playlist.name')
                    ->label('Playlist')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('question.track.title')
                    ->label('Track')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('question.track.artist_name')
                    ->label('Artist')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('question.question_type')
                    ->label('Question Type')
                    ->badge()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('question.prompt_text')
                    ->label('Question')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('question.correct_answer')
                    ->label('Correct Answer')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('sort_order')
                    ->label('Sort Order')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('added_at')
                    ->label('Added At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->defaultSort('sort_order', 'asc');
    }
}

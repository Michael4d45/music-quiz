<?php

declare(strict_types=1);

namespace App\Filament\Resources\MusicTracks\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MusicTracksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                TextColumn::make('artist_name')
                    ->label('Artist')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                TextColumn::make('album_name')
                    ->label('Album')
                    ->searchable()
                    ->limit(30),

                TextColumn::make('release_year')
                    ->label('Year')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('genre')
                    ->searchable()
                    ->limit(20),

                TextColumn::make('duration_ms')
                    ->label('Duration')
                    ->getStateUsing(fn ($record) => $record->duration_ms ? gmdate('i:s', $record->duration_ms / 1000) : null)
                    ->sortable(query: function ($query, $direction) {
                        return $query->orderBy('duration_ms', $direction);
                    }),

                TextColumn::make('subCategory.name')
                    ->label('Category')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('primarySource.name')
                    ->label('Source')
                    ->searchable(),

                TextColumn::make('quiz_questions_count')
                    ->label('Questions')
                    ->getStateUsing(fn ($record) => $record->quizQuestions()->count())
                    ->numeric(),

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

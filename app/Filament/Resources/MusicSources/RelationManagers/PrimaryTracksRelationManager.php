<?php

declare(strict_types=1);

namespace App\Filament\Resources\MusicSources\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PrimaryTracksRelationManager extends RelationManager
{
    protected static string $relationship = 'primaryTracks';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->with([
                'subCategory',
                'primarySource',
            ])->withCount([
                'sourceLinks',
                'quizQuestions',
            ]))
            ->columns([
                TextColumn::make('id')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('artist_name')
                    ->label('Artist')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('album_name')
                    ->label('Album')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('release_year')
                    ->label('Release Year')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('genre')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('duration_ms')
                    ->label('Duration (ms)')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('subCategory.name')
                    ->label('Sub Category')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('primarySource.name')
                    ->label('Primary Source')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('source_links_count')
                    ->label('Source Links')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('quiz_questions_count')
                    ->label('Quiz Questions')
                    ->numeric()
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
                //
            ])
            ->defaultSort('created_at', 'desc');
    }
}

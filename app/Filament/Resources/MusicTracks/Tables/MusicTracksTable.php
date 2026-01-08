<?php

declare(strict_types=1);

namespace App\Filament\Resources\MusicTracks\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class MusicTracksTable
{
    public static function configure(Table $table): Table
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
                SelectFilter::make('sub_category_id')
                    ->label('Sub Category')
                    ->relationship('subCategory', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('primary_source_id')
                    ->label('Primary Source')
                    ->relationship('primarySource', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('genre')
                    ->label('Genre')
                    ->options(
                        fn() => \App\Models\MusicTrack::distinct()
                            ->pluck('genre', 'genre')
                            ->filter()
                            ->sort(),
                    ),
                TernaryFilter::make('source_links_count')
                    ->label('Has Source Links')
                    ->placeholder('All')
                    ->trueLabel('With Links')
                    ->falseLabel('Without Links')
                    ->queries(
                        true: fn($query) => $query->having(
                            'source_links_count',
                            '>',
                            0,
                        ),
                        false: fn($query) => $query->having(
                            'source_links_count',
                            '=',
                            0,
                        ),
                    ),
                TernaryFilter::make('quiz_questions_count')
                    ->label('Has Quiz Questions')
                    ->placeholder('All')
                    ->trueLabel('With Questions')
                    ->falseLabel('Without Questions')
                    ->queries(
                        true: fn($query) => $query->having(
                            'quiz_questions_count',
                            '>',
                            0,
                        ),
                        false: fn($query) => $query->having(
                            'quiz_questions_count',
                            '=',
                            0,
                        ),
                    ),
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

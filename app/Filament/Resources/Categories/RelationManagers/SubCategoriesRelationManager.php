<?php

declare(strict_types=1);

namespace App\Filament\Resources\Categories\RelationManagers;

use App\Filament\Resources\SubCategories\SubCategoryResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SubCategoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'subCategories';

    protected static string|null $relatedResource = SubCategoryResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->searchable(),

                TextColumn::make('description')
                    ->limit(50),

                TextColumn::make('sort_order')
                    ->label('Sort Order')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('music_tracks_count')
                    ->label('Tracks')
                    ->getStateUsing(fn ($record) => $record->musicTracks()->count())
                    ->numeric(),
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
            ->defaultSort('sort_order');
    }
}

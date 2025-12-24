<?php

declare(strict_types=1);

namespace App\Filament\Resources\PlaylistItems\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PlaylistItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('playlist.name')
                    ->label('Playlist')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('question.question_text')
                    ->label('Question')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): string|null {
                        $state = $column->getState();
                        if (is_string($state) && strlen($state) > 50) {
                            return $state;
                        }

                        return null;
                    })
                    ->searchable(),

                TextColumn::make('sort_order')
                    ->label('Sort Order')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('added_at')
                    ->label('Added At')
                    ->dateTime()
                    ->sortable(),
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
            ->defaultSort('sort_order');
    }
}

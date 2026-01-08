<?php

declare(strict_types=1);

namespace App\Filament\Resources\MusicSources\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MusicSourcesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->withCount([
                'apiCredentials',
                'primaryTracks',
                'trackSourceLinks',
            ]))
            ->columns([
                TextColumn::make('id')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('display_name')
                    ->label('Display Name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('icon_url')
                    ->label('Icon URL')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): string|null {
                        $state = $column->getState();
                        if (is_string($state) && strlen($state) > 50) {
                            return $state;
                        }
                        return null;
                    })
                    ->toggleable(),

                TextColumn::make('api_base_url')
                    ->label('API Base URL')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): string|null {
                        $state = $column->getState();
                        if (is_string($state) && strlen($state) > 50) {
                            return $state;
                        }
                        return null;
                    })
                    ->toggleable(),

                IconColumn::make('requires_authentication')
                    ->label('Requires Auth')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('priority')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('api_credentials_count')
                    ->label('API Credentials')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('primary_tracks_count')
                    ->label('Primary Tracks')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('track_source_links_count')
                    ->label('Track Source Links')
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
            ->recordActions([])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('priority');
    }
}

<?php

declare(strict_types=1);

namespace App\Filament\Resources\TrackSourceLinks\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AvailabilitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'availabilities';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn($query) => $query->with(
                    'trackSourceLink',
                    'trackSourceLink.track',
                    'trackSourceLink.source',
                ),
            )
            ->columns([
                TextColumn::make('id')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('trackSourceLink.track.title')
                    ->label('Track')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('trackSourceLink.track.artist_name')
                    ->label('Artist')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('trackSourceLink.source.display_name')
                    ->label('Source')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('country_code')
                    ->label('Country')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Global')
                    ->toggleable(),

                IconColumn::make('is_available')
                    ->label('Available')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('last_checked_at')
                    ->label('Last Checked')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Never checked')
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->defaultSort('country_code', 'asc');
    }
}

<?php

declare(strict_types=1);

namespace App\Filament\Resources\TrackAvailabilities\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TrackAvailabilitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('trackSourceLink.external_id')
                    ->label('Track Source')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('country_code')
                    ->label('Country')
                    ->searchable()
                    ->sortable(),

                IconColumn::make('is_available')
                    ->label('Available')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('last_checked_at')
                    ->label('Last Checked')
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
            ->defaultSort('last_checked_at', 'desc');
    }
}

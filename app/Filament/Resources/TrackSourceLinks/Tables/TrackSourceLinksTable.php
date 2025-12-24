<?php

declare(strict_types=1);

namespace App\Filament\Resources\TrackSourceLinks\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TrackSourceLinksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('track.title')
                    ->label('Track')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('source.name')
                    ->label('Source')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('external_id')
                    ->label('External ID')
                    ->searchable()
                    ->copyable(),

                IconColumn::make('is_verified')
                    ->label('Verified')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-exclamation-triangle')
                    ->trueColor('success')
                    ->falseColor('warning'),

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

                TextColumn::make('availabilities_count')
                    ->label('Regions')
                    ->getStateUsing(fn ($record) => $record->availabilities()->count())
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

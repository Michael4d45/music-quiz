<?php

declare(strict_types=1);

namespace App\Filament\Resources\SourceApiCredentials\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SourceApiCredentialsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('source.name')
                    ->label('Source')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('credential_type')
                    ->label('Type')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('encrypted_value')
                    ->label('Value')
                    ->limit(20)
                    ->formatStateUsing(fn ($state) => '••••••••••••••••••••')
                    ->tooltip('Encrypted - cannot be displayed'),

                TextColumn::make('expires_at')
                    ->label('Expires')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Never'),

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

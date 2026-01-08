<?php

declare(strict_types=1);

namespace App\Filament\Resources\MusicSources\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ApiCredentialsRelationManager extends RelationManager
{
    protected static string $relationship = 'apiCredentials';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->with('source'))
            ->columns([
                TextColumn::make('id')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('source.display_name')
                    ->label('Source')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('credential_type')
                    ->label('Credential Type')
                    ->badge()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('encrypted_value')
                    ->label('Encrypted Value')
                    ->limit(20)
                    ->tooltip(
                        'This value is encrypted and cannot be displayed in plain text',
                    )
                    ->placeholder('Encrypted')
                    ->toggleable(),

                TextColumn::make('expires_at')
                    ->label('Expires At')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Never expires')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('updated_at')
                    ->label('Updated At')
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

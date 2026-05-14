<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->withCount([
                'gameSessions',
                'participants',
                'statistics',
            ]))
            ->columns([
                TextColumn::make('id')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('email_verified_at')
                    ->label('Email Verified')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('verified_google_email')
                    ->label('Google email')
                    ->copyable()
                    ->sortable()
                    ->toggleable(),

                IconColumn::make('is_admin')
                    ->label('Admin')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),

                IconColumn::make('is_guest')
                    ->label('Guest')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('game_sessions_count')
                    ->label('Game Sessions')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('participants_count')
                    ->label('Participants')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('statistics_count')
                    ->label('Statistics')
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
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_admin')
                    ->label('Admin Status')
                    ->placeholder('All')
                    ->trueLabel('Admin')
                    ->falseLabel('Regular User'),
                TernaryFilter::make('is_guest')
                    ->label('Account Type')
                    ->placeholder('All')
                    ->trueLabel('Guest')
                    ->falseLabel('Registered'),
                TernaryFilter::make('email_verified_at')
                    ->label('Email Verified')
                    ->placeholder('All')
                    ->trueLabel('Verified')
                    ->falseLabel('Unverified'),
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

<?php

declare(strict_types=1);

namespace App\Filament\Resources\MusicTracks\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SourceLinksRelationManager extends RelationManager
{
    protected static string $relationship = 'sourceLinks';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn($query) => $query->with([
                    'source',
                    'track',
                ])->withCount('availabilities'),
            )
            ->columns([
                TextColumn::make('id')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('source.name')
                    ->label('Source')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('track.title')
                    ->label('Track')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('external_id')
                    ->label('External ID')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('preview_url')
                    ->label('Preview URL')
                    ->url(fn($record) => $record->preview_url)
                    ->openUrlInNewTab()
                    ->limit(30)
                    ->tooltip(fn($record) => $record->preview_url)
                    ->toggleable(),

                TextColumn::make('full_url')
                    ->label('Full URL')
                    ->url(fn($record) => $record->full_url)
                    ->openUrlInNewTab()
                    ->limit(30)
                    ->tooltip(fn($record) => $record->full_url)
                    ->toggleable(),

                IconColumn::make('is_verified')
                    ->label('Verified')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable()
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

                TextColumn::make('availabilities_count')
                    ->label('Availabilities')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('last_checked_at')
                    ->label('Last Checked')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('source_id')
                ->label('Source')
                ->relationship('source', 'name')
                ->required()
                ->searchable()
                ->preload(),

            TextInput::make('external_id')
                ->label('External ID')
                ->required()
                ->maxLength(255),

            TextInput::make('preview_url')
                ->label('Preview URL')
                ->url()
                ->maxLength(500),

            TextInput::make('full_url')
                ->label('Full URL')
                ->url()
                ->maxLength(500),

            TextInput::make('embed_url')
                ->label('Embed URL')
                ->url()
                ->maxLength(500),

            TextInput::make('album_art_url')
                ->label('Album Art URL')
                ->url()
                ->maxLength(500),

            Toggle::make('is_verified')->label('Is Verified')->default(false),

            Toggle::make('is_available')->label('Is Available')->default(true),

            DateTimePicker::make('last_checked_at')
                ->label('Last Checked At')
                ->default(now()),
        ]);
    }
}

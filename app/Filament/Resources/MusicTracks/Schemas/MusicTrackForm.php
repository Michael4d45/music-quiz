<?php

declare(strict_types=1);

namespace App\Filament\Resources\MusicTracks\Schemas;

use App\Enums\MusicTrackOriginKind;
use App\Filament\Resources\MusicSources\MusicSourceResource;
use App\Filament\Resources\SubCategories\SubCategoryResource;
use App\Filament\Resources\Users\UserResource;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Schema;

class MusicTrackForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->required()->maxLength(255),

            TextEntry::make('user.name')
                ->label('User')
                ->url(static fn($record) => (
                    $record && $record->user_id
                        ? UserResource::getUrl('edit', [
                            'record' => $record->user_id,
                        ]) : null
                ))
                ->placeholder('N/A'),

            Select::make('user_id')
                ->relationship('user', 'name')
                ->label('User')
                ->searchable(),

            TextInput::make('artist_name')
                ->label('Artist')
                ->required()
                ->maxLength(255),

            TextInput::make('album_name')->label('Album')->maxLength(255),

            TextInput::make('release_year')->label('Release Year')->numeric(),

            TextInput::make('genre')->maxLength(255),

            TextInput::make('duration_ms')
                ->label('Duration (ms)')
                ->numeric()
                ->minValue(1000),

            Select::make('origin_kind')
                ->label('Origin / grouping kind')
                ->options(MusicTrackOriginKind::class)
                ->native(false),

            TextInput::make('origin_title')
                ->label('Game, film, TV show, or soundtrack title')
                ->maxLength(255),

            TextInput::make('user_upload_original_name')
                ->label('Uploaded file name')
                ->disabled()
                ->dehydrated(false),

            TextInput::make('user_upload_path')
                ->label('Uploaded file storage path')
                ->disabled()
                ->dehydrated(false),

            TextEntry::make('subCategory.name')
                ->label('Sub Category')
                ->url(static fn($record) => (
                    $record && $record->sub_category_id
                        ? SubCategoryResource::getUrl('edit', [
                            'record' => $record->sub_category_id,
                        ]) : null
                ))
                ->placeholder('N/A'),

            Select::make('sub_category_id')
                ->relationship('subCategory', 'name')
                ->label('Sub Category')
                ->required()
                ->searchable(),

            TextEntry::make('primarySource.name')
                ->label('Primary Source')
                ->url(static fn($record) => (
                    $record && $record->primary_source_id
                        ? MusicSourceResource::getUrl('edit', [
                            'record' => $record->primary_source_id,
                        ]) : null
                ))
                ->placeholder('N/A'),

            Select::make('primary_source_id')
                ->relationship('primarySource', 'name')
                ->label('Primary Source')
                ->required()
                ->searchable(),
            Flex::make([
                TextInput::make('id')->copyable()->disabled(),
                DateTimePicker::make('created_at')->disabled(),
                DateTimePicker::make('updated_at')->disabled(),
            ])->columnSpanFull()->hiddenOn('create'),
        ]);
    }
}

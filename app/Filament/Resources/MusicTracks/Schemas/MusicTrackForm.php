<?php

declare(strict_types=1);

namespace App\Filament\Resources\MusicTracks\Schemas;

use App\Models\MusicSource;
use App\Models\SubCategory;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MusicTrackForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),

                TextInput::make('artist_name')
                    ->label('Artist')
                    ->required()
                    ->maxLength(255),

                TextInput::make('album_name')
                    ->label('Album')
                    ->maxLength(255),

                TextInput::make('release_year')
                    ->label('Release Year')
                    ->numeric()
                    ->minValue(1900)
                    ->maxValue(date('Y') + 1),

                TextInput::make('genre')
                    ->maxLength(255),

                TextInput::make('duration_ms')
                    ->label('Duration (ms)')
                    ->numeric()
                    ->minValue(1000),

                Select::make('sub_category_id')
                    ->label('Sub Category')
                    ->options(SubCategory::pluck('name', 'id'))
                    ->required()
                    ->searchable(),

                Select::make('primary_source_id')
                    ->label('Primary Source')
                    ->options(MusicSource::pluck('name', 'id'))
                    ->required()
                    ->searchable(),
                TextInput::make('id')->required()->disabled(),
                DateTimePicker::make('created_at')->disabled(),
                DateTimePicker::make('updated_at')->disabled(),

            ]);
    }
}
